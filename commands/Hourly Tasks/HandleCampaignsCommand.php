<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Processes the entries inserted by the 'pmd:2hourcheck' command and updates the scheme status field
 * based on whether the reading was completed successfully or not
 *
 * Class CheckSchemesCommand
 */
class HandleCampaignsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'campaigns';

	private $testMode = false;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle running campaigns';
	
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->log = new Logger('Handle running campaigns');
        $this->log->pushHandler(new StreamHandler(__DIR__ . '/HandleCampaignsCommand/' . date('Y-m-d') . '.log'), Logger::INFO);
		
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {	
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 0);
		
		try {
			
			
			$this->processNewCampaigns();
			$this->processNewCampaignsNotifications();
			$this->processActiveCampaigns();
			
		} catch(Exception $e) {
			
			echo $e->getMessage() . " (" . $e->getLine() . ")";
			
		}
		
    }
	
	public function processNewCampaigns()
	{
		
		try {
			
			echo "\n\n";
			
			$campaigns = Campaign::whereRaw("(announcement_id IS NULL OR announcement_id = 0)")->get();
			
			
			foreach($campaigns as $k => $c) {
				
				echo "Processing campaign #" . $c->id . "\n";
				echo "Title: " . $c->title . "\n";
				
				$announcement = Announcement::where('campaign_id', $c->id)->first();
				if(!$announcement) {
					$announcement = new Announcement();
				} 
				$announcement->teaser = $c->teaser;
				$announcement->title = $c->title;
				$announcement->body = $c->body;
				$announcement->img = $c->icon_img;
				$announcement->show_at = $c->show_from;
				$announcement->stop_show_at = $c->show_to;
				$announcement->date = $c->show_at;
				$announcement->campaign_id = $c->id;
				$announcement->save();
				
				
				$announcement = Announcement::where('campaign_id', $c->id)->first();
				
				if(!$announcement) {
					echo "Cannot proceed further. Announcement creation failed!\n";
					continue;
				}
				
				$announcement_id = 0;
				if($announcement) {
					$announcement_id = $announcement->id;
					$c->announcement_id = $announcement_id;
					$c->save();
				}
				
				echo "Announcement created: #" . $announcement->id . "\n";
				
				echo "\n\n";
			}
			
		} catch(Exception $e) {
			
			echo $e->getMessage() . " (" . $e->getLine() . ")";
			
		}
		
		echo "\n\n";
		
	}
	
	public function processNewCampaignsNotifications()
	{
		try {
			
			echo "\n";
			
			$campaigns = Campaign::whereRaw("(notifs_sent = 0)")->get();
			
			
			foreach($campaigns as $k => $c) {
				
				
				$time_to_send = ((new DateTime(date('Y-m-d'))) >= (new DateTime($c->show_from)));
				
				
				echo "Processing campaign notifications #" . $c->id . "\n";
				echo "Title: " . $c->title . "\n";
				
				
				if(!$time_to_send) {
					echo "Cannot proceed further yet. (" . Carbon\Carbon::parse($c->show_from)->diffForHumans() . ")\n";
					continue;
				} else {
					Campaign::where('active', 1)
					->update([
						'active' 		=> 0,
						'stopped_at'	=> date('Y-m-d H:i:s'),
					]);
				}
				
				$notifs_sent = 0;
				$announcement = Announcement::where('campaign_id', $c->id)->first();
				if(!$announcement) {
					echo "Cannot proceed further. Announcement creation failed!\n";
					continue;
				}
				
				if($c->notif_button_txt == "n/a") {
					$c->notifs_sent = 1;
					$c->save();	
					echo "Cannot proceed further.This campaign has opted out of notifications.\n";
					continue;
				}
				
				
				
					$customers = Customer::select('customers.id as id', 'customers.scheme_number as scheme_number')->join('schemes', 'customers.scheme_number', '=', 'schemes.id')
					->whereRaw('(customers.deleted_at IS NULL AND schemes.archived = 0 OR customers.id = 1)')
					->whereRaw('(customers.status = 1 OR customers.simulator > 0)')->get();
			
					foreach($customers as $k => $customer) {
						
						$notification = InAppNotification::where('customer_id', $customer->id)
						->where('campaign_id', $c->id)->first();
						
						if(!$notification) {
							$notification = new InAppNotification();
						}
						
						$notification->customer_id = $customer->id;
						$notification->scheme_number = $customer->scheme_number;
						$notification->all_schemes = true;
						$notification->img = $c->icon_img;
						$notification->title = $c->title;
						//$notification->body = "<img src='" . $c->icon_img . "'/><br/>" . $c->teaser;
						$notification->body = $c->notif_button_body;
						$notification->campaign_id = $c->id;
						$notification->dismiss_txt = $c->notif_button_txt;
						$notification->dismiss_txt_url = "/users/announcement-view/" . $announcement->id;
						$notification->save();

						$notifs_sent++;
					}
					
					$c->active = 1;
					$c->notifs_sent = $notifs_sent;
					$c->save();
			
				
				echo "Sent $notifs_sent campaign notifications.\n";
				
				echo "\n\n";
			}
			
		} catch(Exception $e) {
			
			echo $e->getMessage() . " (" . $e->getLine() . ")";
			
		}
		
		echo "\n\n";
		
	}

	public function processActiveCampaigns()
	{
		try {
			
			echo "\n";
			
			$campaigns = Campaign::whereRaw("(active = 1)")->get();
			
			
			foreach($campaigns as $k => $c) {
				
				$announcement = $c->announcement;
				
				
				echo "Processing running campaign #" . $c->id . "\n";
				echo "Title: " . $c->title . "\n";
				
				
				if(!$announcement) {
					echo "Campaign has no announcement. Aborting..\n";
					continue;
				}
				
				$c->seen_by = $announcement->total_views;
			
				$seen_by = [];
				$announcement_views = AnnouncementView::where('announcement_id', $announcement->id)
				->get();
				
				foreach($announcement_views as $j => $v) {
					
					$customer = Customer::find($v->customer_id);
					
					if(!$customer) continue;
					
					array_push($seen_by, $customer->username);
				}
				
				$c->interact_seen_by = serialize($seen_by);
				$c->save();
				
				echo "\n\n";
			}
				
		} catch(Exception $e) {
			
			echo $e->getMessage() . " (" . $e->getLine() . ")";
			
		}
		
		echo "\n\n";
				
	}
	
    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }
}