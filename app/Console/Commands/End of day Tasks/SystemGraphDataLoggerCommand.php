<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SystemGraphDataLoggerCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'system:graphdata';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Log system data for graphical usage, display usage for the dashboard.';

	/**
	 *
	 * Set the log file & log file name
	 *
	 */
	private function setLog($name = "default") 
	{
		$this->log = new Logger($name);
		$this->log->pushHandler(new StreamHandler(__DIR__ . "/SystemGraphDataLoggerCommand/" . date('Y-m-d') . ".log", Logger::INFO));
	}
	
	/**
	 *
	 * Create a log entry in the log file
	 *
	 */
	private function createLog($msg, $print = true)
	{
		$this->log->info($msg);
		
		if($print) {
			$this->info($msg);
		}
	}

	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		
		$time_start = microtime(true);
		$this->logTickets();
		$this->logSMS();
		$this->logAppEngagement();
		$this->logTopupMethodsNew();
		$this->logMisc();
		$this->logFaqs();
		$this->logTopupTrends();
		$this->logStripe();
		$this->setLog();
		$this->logDailyCustomerStatus();
		$this->logBalances();
		$this->logShutOffs();
		$this->logStatements();
		$this->logAwayModes();
		$this->logIOUs();
		$this->logTopups();
		$this->logTCP();
		$this->logTopupMethods();
		$this->logSchemeUptime();
		$this->logSchemesAvg();
		
		
		$this->info("Total execution time: " . (microtime(true) - $time_start) . " seconds");
		
	}	
	
	public function logStripe()
	{
		
		$today = $this->todaysEntry();
		Stripe::start();
		$data = [
			'payouts' => [],
			'total_in_transit' => 0,
			'total_pending' => 0,
			'total_20' => 0,
			'total_25' => 0,
			'total_35' => 0,
			'total_50' => 0,
			'total_100' => 0,
		];
		
		$last_50_payouts = Stripe::getPayouts(7, false);
		
		foreach($last_50_payouts as $k => $v) {
			$data['payouts'][] = $v;
			if($v->status == 'pending' || $v->status == 'in_transit') {
				$data['total_in_transit'] += $v->amount;
			}
		}
		
		$data['total_20'] = PaymentStorage::where('acceptor_name_location_', 'stripe')->where('amount', 20)->count();
		$data['total_25'] = PaymentStorage::where('acceptor_name_location_', 'stripe')->where('amount', 25)->count();
		$data['total_35'] = PaymentStorage::where('acceptor_name_location_', 'stripe')->where('amount', 35)->count();
		$data['total_50'] = PaymentStorage::where('acceptor_name_location_', 'stripe')->where('amount', 50)->count();
		$data['total_100'] = PaymentStorage::where('acceptor_name_location_', 'stripe')->where('amount', 100)->count();
		
		//$this->info('test');
		
		$data['total_pending'] = Stripe::getPendingBalance();
		
		$res = $today->set('stripe', $data);

		$this->info("logStripe: " . serialize($res));
		
		
	}
	
	public function logTopupMethodsNew()
	{
		$today = $this->todaysEntry();
		$months = [];
		$months_data_labels = [];
		$months_data = [];
		for ($i = 0; $i <= 11; $i++) {
			$months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
			$months_data_labels[] = "'" . date("M-y", strtotime( date( 'Y-m-01' )." -$i months")) . "'";
			$df = date("M-y", strtotime( date( 'Y-m-01' )." -$i months"));
			$months_data[$df] = [
				"paypal" => 0,
				"payzone" => 0,
				"paypoint" => 0,
				"stripe" => 0
			];
		}
		$months_data_labels = array_reverse($months_data_labels);
		$start = $months[count($months)-1];
		$end = $months[0];
		
		//echo "Start: " . $start . "\n";
		//echo "End: " . $end . "\n";
		$topups = PaymentStorage::whereRaw("(settlement_date >= '$start-01' AND settlement_date <= '$end-31')")->get();
		
		$data = [
			"paypal" => [],
			"payzone" => [],
			"paypoint" => [],
			"stripe" => []
		];
		
		foreach($data as $k => $d) {	
			for($i=0; $i<12; $i++) {
				$data[$k][] = [];
			}	
		}
		
		//echo count($data['paypal']);
		
		foreach($topups as $k => $v) {
			
			$month = date("M-y", strtotime($v->settlement_date));
			$paymentType = "";
			
			if(substr($v->ref_number, 0, 6) == "PAYID-" || substr($v->ref_number, 0, 4) == "PAY-" || $v->acceptor_name_location == "paypal" || $v->acceptor_name_location_ == "paypal") {
				$paymentType = "paypal";
			}
		
			if($v->acceptor_name_location_ == "paypoint" || $v->acceptor_name_location == "paypoint" || substr($v->ref_number, 0, 3) == "PPR") {
				$paymentType = "paypoint";
			}
			
			if($v->acceptor_name_location_ == "payzone" || $v->acceptor_name_location == "payzone" || substr($v->ref_number, 0, 3) == "PZ-") {
				$paymentType = "payzone";
			}
			
			if($v->acceptor_name_location_ == "stripe" || $v->acceptor_name_location == "stripe" || substr($v->ref_number, 0, 3) == "ch_") {
				$paymentType = "stripe";
			}
			
			if($paymentType == "") {
				//echo $v->ref_number . "\n";
				continue;
			}
			
			if(isset($months_data[$month]) && $paymentType != "") {
				$months_data[$month][$paymentType]++;
			}
			
			// $month_index = $month - 1;
			
			
			// $data[$paymentType][$month_index]++;
			
		}
		
		$res = $today->set('topup_12_labels', $months_data_labels);
		$res = $today->set('topup_12_data', $months_data);

		 $this->info("logTopupMethodsNew: " . serialize($res));
		
	}
	
	/**
	 *
	 * Log Green, Yellow, Red & Blue Customers 
	 *
	**/
	private function logDailyCustomerStatus()
	{
		
		$today = $this->todaysEntry();
		$yesterday = $this->yesterdaysEntry();
		
		$customers = System::getCustomers();
		$customers_yesterday = $yesterday->get('customer_status');
	
		
		$data = [
			'greenCustomers'				=> count($customers->green),
			'yellowCustomers'				=> count($customers->yellow),
			'redCustomers'					=> count($customers->red),
			'blueCustomers'					=> count($customers->blue),
			'whiteCustomers'				=> count($customers->white),
			'greenCustomers_yesterday'		=> $customers_yesterday['greenCustomers'],
			'yellowCustomers_yesterday'		=> $customers_yesterday['yellowCustomers'],
			'redCustomers_yesterday'		=> $customers_yesterday['redCustomers'],
			'blueCustomers_yesterday'		=> $customers_yesterday['blueCustomers'],
			'whiteCustomers_yesterday'		=> $customers_yesterday['whiteCustomers'],
			'greenCustomers_pc'				=> (((count($customers->green) - $customers_yesterday['greenCustomers']) / $customers_yesterday['greenCustomers'] )*100),
			'redCustomers_pc'				=> (((count($customers->red) - $customers_yesterday['redCustomers']) / $customers_yesterday['redCustomers'] )*100),
			'yellowCustomers_pc'			=> (((count($customers->yellow) - $customers_yesterday['yellowCustomers']) / $customers_yesterday['yellowCustomers'] )*100),
			'blueCustomers_pc'				=> (((count($customers->blue) - $customers_yesterday['blueCustomers']) / $customers_yesterday['blueCustomers'] )*100),
			'whiteCustomers_pc'				=> ($customers_yesterday['whiteCustomers'] != 0) ? (((count($customers->white) - $customers_yesterday['whiteCustomers']) / $customers_yesterday['whiteCustomers'] )*100) : 0,
		];
		/*
		foreach($data as $k => $v) {
			$this->info($k . " => " . $v);
		}*/
		
		$res = $today->set('customer_status', $data);

		$this->info("logDailyCustomerStatus: " . serialize($res));
	}
	
	/**
	*
	* Log balances of accounts@prepago.ie & noreply@snugzone.biz
	*
	*/
	private function logBalances()
	{
		
		$today = $this->todaysEntry();
		$balances = Paypal::getBal();
		
		$res = $today->set('balances', [
			'0' => $balances->accounts,
			'1' => $balances->noreply,
		]);
		
		$total_balance = $balances->accounts + $balances->noreply;
		
		$current_max = System::get('max_balance');
		$current_min = System::get('min_balance');
		$current_avg = System::get('avg_balance');

		if($current_max < $total_balance)
			System::set('max_balance', $total_balance);
		
		if($current_min > $total_balance)
			System::set('min_balance', $total_balance);
		
		if($current_avg == 0) {
			System::set('avg_balance', $total_balance);
		} else {
			$current_avg = ($current_avg + $total_balance) / 2;
			System::set('avg_balance', $current_avg);
		}
		
		$this->info("logBalances: " . serialize($res));
	}
	
	/**
	*
	* Log # of shut offs today
	*/
	private function logShutOffs()
	{
		$today = $this->todaysEntry();
		$shutOffs = DistrictHeatingMeter::whereRaw("(last_shut_off_time LIKE '%" . date('Y-m-d') . "%')")->get();
		$restored = 0;
		$shutoffs_restored_list = [];
		$shutoffs_unrestored_list = [];
			
		foreach($shutOffs as $s) {
			$customer = $s->customer;
			if($customer) {
				$customer_pkg = (object)[
					'id' => $customer->id,
					'username' => $customer->username,
					'temp' => $s->last_flow_temp,
					'balance' => $customer->balance,
					'last_topup' => $customer->lastTop,
					'restored' => ($customer->shut_off == 0),
				];
				if($customer_pkg->restored) {
					array_push($shutoffs_restored_list, $customer_pkg);
				} else {
					array_push($shutoffs_unrestored_list, $customer_pkg);
				}
				if($customer->shut_off == 0)
				{
					$restored++;
				}
			}
		}
		
		$res = $today->set('shutoffs', count($shutOffs));
		$res = $today->set('shutoffs_restored', $restored);
		$res = $today->set('shutoffs_restored_list', $shutoffs_restored_list);
		$res = $today->set('shutoffs_unrestored_list', $shutoffs_unrestored_list);
		
		$this->info("logShutOffs: " . serialize($res));
	}

	/**
	*
	* Log # of statements issued today
	*/
	private function logStatements()
	{
		$today = $this->todaysEntry();
		$statements = SnugzoneAppStatement::whereRaw("(created_at LIKE '%" . date('Y-m-d') . "%')")->get();
		
		$res = $today->set('statements', count($statements));

		$this->info("logStatements: " . serialize($res));
	}

	
	/**
	*
	* Log # of away modes used today
	*/
	private function logAwayModes()
	{
		$today = $this->todaysEntry();
		$awayModes = RemoteControlLogging::whereRaw("(date_time LIKE '%" . date('Y-m-d') . "%')")
		->whereRaw("(action LIKE '%Away Mode Starting%')")
		->get();
		
		$res = $today->set('awaymodes', count($awayModes));
		
		$this->info("logAwayModes: " . serialize($res));
	}
	

	/**
	*
	* Log # of away modes used today
	*/
	private function logIOUs()
	{
		$today = $this->todaysEntry();
		$IOUs = IOUStorage::whereRaw("(time_date LIKE '%" . date('Y-m-d') . "%')")
		->get();
		
		$res = $today->set('ious', count($IOUs));
		
		$this->info("logIOUs: " . serialize($res));
	}
	
	
	/**
	*
	* Log # of topup's today & this week
	*/
	private function logTopups()
	{
		
		$today = $this->todaysEntry();
		$ppTopupsToday = PaymentStorage::whereRaw("(time_date LIKE '%" . date('Y-m-d') . "%' AND ref_number LIKE '%PAYID-%')")->count();
		$pzTopupsToday = PaymentStorage::whereRaw("(time_date LIKE '%" . date('Y-m-d') . "%' AND ref_number LIKE '%PZ-%')")->count();
		$totalTopupsToday = PaymentStorage::whereRaw("(time_date LIKE '%" . date('Y-m-d') . "%')")->count();
		$totalTopupsAmount = PaymentStorage::whereRaw("(time_date LIKE '%" . date('Y-m-d') . "%')")->sum('amount');
		
		$day = date('w');
		$week_start = date("Y-m-d", strtotime('monday this week')) . " 00:00:00";
		$week_end = date("Y-m-d", strtotime('sunday this week')) . " 23:59:59";

		$ppTopupsWeek = PaymentStorage::whereRaw("(time_date >= '$week_start' AND time_date <= '$week_end' AND ref_number LIKE '%PAYID-%')")->count();
		$pzTopupsWeek = PaymentStorage::whereRaw("(time_date >= '$week_start' AND time_date <= '$week_end' AND ref_number LIKE '%PZ-%')")->count();
		$totalTopupsWeek = PaymentStorage::whereRaw("(time_date >= '$week_start' AND time_date <= '$week_end')")->count();
		
		$res = $today->set('pptopupstoday', $ppTopupsToday);
		$res = $today->set('pztopupstoday', $pzTopupsToday);
		$res = $today->set('pptopupsweek', $ppTopupsWeek);
		$res = $today->set('pztopupsweek', $pzTopupsWeek);
		$res = $today->set('totaltopupstoday', $totalTopupsToday);
		$res = $today->set('totaltopupsamount', $totalTopupsAmount);
		$res = $today->set('totaltopupsweek', $totalTopupsWeek);
		//
		$this->logTopupsRevenue();
	}
	
	private function logTopupsRevenue()
	{
		ini_set('memory_limit', '-1');
		
		$today = $this->todaysEntry();
		
		$lastYearY = date("Y",strtotime("-1 year"));
		$curYearY = date("Y");
		$lastYear = [];
		$thisYear = [];
		$customers = [];
		$totalLastYear = 0;
		$totalCurYear = 0;
		$topupPercent = 0;
		$customersPercent = 0;
		
		$topupsLastYear = PaymentStorage::whereRaw("YEAR(time_date) >= '$lastYearY' AND YEAR(time_date) < '$curYearY'")
		->orderBy(DB::raw('time_date'))
		->get()
		->groupBy(function($d) {
			 return Carbon\Carbon::parse($d->time_date)->format('m');
		});
		$topupsCurYear = PaymentStorage::whereRaw("YEAR(time_date) > '$lastYearY' AND YEAR(time_date) <= '$curYearY'")
		->orderBy(DB::raw('time_date'))
		->get()
		->groupBy(function($d) {
			 return Carbon\Carbon::parse($d->time_date)->format('m');
		});
		
		foreach($topupsLastYear as $t) {
			$date = Carbon\Carbon::parse($t[0]->time_date)->format('d M Y');
			$lastYear[] = count($t);	
			if(Carbon\Carbon::parse($t[0]->time_date)->format('m') <= $topupsCurYear->count()) {
				$totalLastYear += count($t);
			}
		}
		
		foreach($topupsCurYear as $t) {
			$date = Carbon\Carbon::parse($t[0]->time_date)->format('d M Y');
			$thisYear[] = count($t);
			$totalCurYear += count($t);
		}
		
		$topupPercent = ( ($totalCurYear - $totalLastYear) / $totalLastYear ) * 100;
		
		$months = DataSet::getLastYear();
		foreach($months['months'] as $k => $m) {
			$customers[] = Customer::customerCountAtPeriod($m . '-31');
		}
		
		$customersPercent = (($customers[count($customers)-1] - $customers[0]) / $customers[0]) * 100; 
		
		$data = [
			'lastYear'			=> $lastYear,
			'thisYear'			=> $thisYear,
			'customers'			=> $customers,
			'customersLastYear'	=> $customers[0],
			'totalLastYear' 	=> $totalLastYear,
			'totalCurYear'		=> $totalCurYear,
			'topupPercent'		=> $topupPercent,
			'customersPercent'	=> $customersPercent,
		];
		
		$res = $today->set('topups_revenue', $data);
	}
	
	/**
	*
	* Log # of support issue tickets this month
	*
	*/
	private function logTickets()
	{
		
		$today = $this->todaysEntry();
		$month_start = date('Y-m-d', strtotime('first day of this month')) . ' 00:00:00';
		$month_end = date('Y-m-d', strtotime('last day of this month')) . ' 23:59:59';
		
		$supportIssues = SupportIssue::whereRaw("(created_at >= '$month_start' AND created_at <= '$month_end')")->count();
		
		$res = $today->set('ticketsmonth', $supportIssues);
		
		$this->info("logTickets: " . serialize($res));
		
		
		// App Support responses
		
		$bug_reports_last_13_wks_data = ReportABug::whereRaw("(created_at >= DATE(NOW()) - INTERVAL 13 WEEK)")->get();
		$bug_reports_last_13_wks = [];
		foreach($bug_reports_last_13_wks_data as $k => $b) {
			$week = date('W', strtotime($b->created_at));
			if(!isset($bug_reports_last_13_wks[$week])) {
				$bug_reports_last_13_wks[$week] = [
					'total' => 0,
					'total_replies' => 0,
					'unknown' => 0,
					'happy' => 0,
					'unhappy' => 0,
				];
			}
			
			$bug_reports_last_13_wks[$week]['total']++;
			
			if(strlen($b->follow_up_at) > 3) {
				$bug_reports_last_13_wks[$week]['total_replies']++;
				if($b->follow_up_res == 1) {
					$bug_reports_last_13_wks[$week]['happy']++;
				} else {
					$bug_reports_last_13_wks[$week]['unhappy']++;
				}
			} else {
				$bug_reports_last_13_wks[$week]['unknown']++;
			}
		}
		
		//echo var_dump($bug_reports_last_13_wks);
		
		
		$bug_reports_last_30 = ReportABug::whereRaw("(created_at >= DATE(NOW()) - INTERVAL 30 DAY)")->get();
		$bug_reports_all_time = ReportABug::all();
		$reports_responses = 0;
		$reports_happy = 0;
		$reports_unhappy = 0;
		$follow_up_reply = [

		];
		$all_time = [
			"count" => 0,
			"responses" => 0,
			"happy" => 0,
			"unhappy" => 0,
		];
		

		foreach($bug_reports_last_30 as $k => $v) {
			if(strlen($v->follow_up_at) > 3) {
				
				if(strlen($v->follow_up_reply) > 2) {
					if(!isset($follow_up_reply[$v->follow_up_reply])) {
						$follow_up_reply[$v->follow_up_reply] = 0;
					}
					$follow_up_reply[$v->follow_up_reply]++;
				} else {
					if(!isset($follow_up_reply['None'])) {
						$follow_up_reply['None'] = 0;
					}
					$follow_up_reply['None']++;
				}
				
				$reports_responses++;
				if($v->follow_up_res == 1) {
					$reports_happy++;
				} else {
					$reports_unhappy++;
				}
			}
		}
		
		
		$all_time["count"] = count($bug_reports_all_time);
		foreach($bug_reports_all_time as $k => $v) {
			if(strlen($v->follow_up_at) > 3) {
				$all_time["responses"]++;
				if($v->follow_up_res == 1) {
					$all_time["happy"]++;
				} else {
					$all_time["unhappy"]++;
				}
			}
		}
		
		// echo count($bug_reports_last_30) . "\n";
		// echo $reports_responses . "\n";
		// echo $reports_happy . "\n";
		// echo $reports_unhappy . "\n";

		$data = [
			"last_30_days" => count($bug_reports_last_30),
			"last_13_wks" => $bug_reports_last_13_wks,
			"responses" => $reports_responses,
			"happy" => $reports_happy,
			"unhappy" => $reports_unhappy,
			"follow_up_reply" => $follow_up_reply,
			"all_time"	=> $all_time,
		];
		
		$res = $today->set('support', $data);
		
		$this->info("logSupport: " . serialize($res));
		
	}
	
	private function logSMS()
	{
		
		$today = $this->todaysEntry();
		$yesterday = $this->yesterdaysEntry();
		
		$response = file_get_contents("http://rest.sendmode.com/v2/credits?access_key=EHJH6YEXNKRUO1YXGA0C");
		$balance = 0;
		$used_today = 0;
		
		try {
			
			$parsed = json_decode($response);
		
			if(is_object($parsed)) {
				if(isset($parsed->balance)) {
					$balance = $parsed->balance; 
				}
			}
			
		} catch(Exception $e) {
			echo $e->getMessage() . " (" . $e->getLine() . ")";
		}
		
		if( ($yesterday->contains('sms_api')) ) {
			$yesterday_data = $yesterday->get('sms_api');
			$credit_yesterday = $yesterday_data['credit'];
			$used_today = $credit_yesterday - $balance;
			if($used_today <= 0) {
				$increase = abs($balance - $credit_yesterday);
				$bought_creds = 10000;
				if($increase >= 4000 && $increase < 6000)
					$bought_creds = 5000;
				if($increase >= 9000 && $increase < 12000)
					$bought_creds = 10000;
				if($increase >= 13000 && $increase < 19000)
					$bought_creds = 15000;
				if($increase >= 22000 && $increase < 29000)
					$bought_creds = 25000;
				
				$used_today = $bought_creds - $increase;			
			}
		}
		
	
		
		$res = $today->set('sms_api', [
			'credit' => $balance,
			'used_today' => $used_today,
		]);
		
		$this->info("logSMS: " . serialize($res));
		
		
	}
	
	/**
	*
	* Log temperature Control panel categories (need restoration, need shutoff, need awaymode)
	*
	*/
	private function logTCP()
	{
		$today = $this->todaysEntry();
		$require_shut_off = DistrictHeatingMeter::requireShutoff()->count();
		$require_restoration = DistrictHeatingMeter::requireRestoration()->count();
		
		
		$res = $today->set('tcp', [
			'require_shut_off' => $require_shut_off,
			'require_restoration' => $require_restoration,
		]);
		
		$this->info("logTCP: " . serialize($res));
		
	}
	
	
	// TO FINISH
	private function logAppEngagement()
	{
		
		// Global vars
		$today = $this->todaysEntry();
		$new_app_growth = [];
		$old_app_growth = [];
		$cur_year = date('Y');
		$last_year = date('Y') - 1;
		$months = [ "Jan_$last_year" => [], "Feb_$last_year" => [], "Mar_$last_year" => [], "Apr_$last_year" => [], "May_$last_year" => [], "Jun_$last_year" => [], "Jul_$last_year" => [], "Aug_$last_year" => [], "Sep_$last_year" => [], "Oct_$last_year" => [], "Nov_$last_year" => [], "Dec_$last_year" => [], "Jan_$cur_year" => [], "Feb_$cur_year" => [], "Mar_$cur_year" => [], "Apr_$cur_year" => [], "May_$cur_year" => [], "Jun_$cur_year" => [], "Jul_$cur_year" => [], "Aug_$cur_year" => [], "Sep_$cur_year" => [], "Oct_$cur_year" => [], "Nov_$cur_year" => [], "Dec_$cur_year" => [], ];
		
		/*
			Calculate app platforms
		*/
		$totalIOS = CustomerEngagement::whereRaw("(platform = 'iOS')")->count();
		$totalAndroid = CustomerEngagement::whereRaw("(platform = 'Android')")->count();
		$totalBrowser = CustomerEngagement::whereRaw("(platform = 'browser')")->count();
		$data_platforms = [
			"ios" => $totalIOS,
			"android" => $totalAndroid,
			"browser" => $totalBrowser,
		];	
		$res = $today->set('app_platforms', $data_platforms);
		
		
		/*
			Calculate unique app logins
		*/
		
		//try {
		
			// Step 1. Declare Months & Array
			$unique_new_this_year_months_data = [ "Jan" => [], "Feb" => [], "Mar" => [], "Apr" => [], "May" => [], "Jun" => [], "Jul" => [], "Aug" => [], "Sep" => [], "Oct" => [], "Nov" => [], "Dec" => [], ];
			$unique_new_last_year_months_data = [ "Jan" => [], "Feb" => [], "Mar" => [], "Apr" => [], "May" => [], "Jun" => [], "Jul" => [], "Aug" => [], "Sep" => [], "Oct" => [], "Nov" => [], "Dec" => [], ];
			$unique_new_logins = CustomerEngagement::whereRaw("( YEAR(date_added) = YEAR(CURDATE()) OR YEAR(date_added) = YEAR(CURDATE())-1 )")->get();
			
			// Step 2. Fill Month Arrays with customer ID's
			foreach($unique_new_logins as $k => $v) {
				
				$month_prefix = date('M', strtotime($v->updated_at));
				$year = date('Y', strtotime($v->updated_at));
				$prefix = $month_prefix . "_" . $year;
				$customer = $v->customer_id;
				
				if($year == $cur_year) {
					if(!in_array($customer, $unique_new_this_year_months_data[$month_prefix])) {
						$unique_new_this_year_months_data[$month_prefix][] = $customer;
					}
				} else {
					if(!in_array($customer, $unique_new_last_year_months_data[$month_prefix])) {
						$unique_new_last_year_months_data[$month_prefix][] = $customer;
					}
				}
				
			}
			
			
			// Step 3. Change Month Array Values to the count of customers in them instad
			foreach($unique_new_last_year_months_data as $k => $month) {
				$count = count($unique_new_last_year_months_data[$k]);
				$unique_new_last_year_months_data[$k] = $count;
				$new_app_growth[] = $count;
			}
			foreach($unique_new_this_year_months_data as $k => $month) {
				$count = count($unique_new_this_year_months_data[$k]);
				$unique_new_this_year_months_data[$k] = $count;
				$new_app_growth[] = $count;
			}
			
			// Step x. Test
			foreach($new_app_growth as $k => $v) {
					
					
				//echo $v . "\n";
					
			}
			
			
			// Step 1. Declare Months & Array
			$unique_old_this_year_months_data = [ "Jan" => [], "Feb" => [], "Mar" => [], "Apr" => [], "May" => [], "Jun" => [], "Jul" => [], "Aug" => [], "Sep" => [], "Oct" => [], "Nov" => [], "Dec" => [], ];
			$unique_old_last_year_months_data = [ "Jan" => [], "Feb" => [], "Mar" => [], "Apr" => [], "May" => [], "Jun" => [], "Jul" => [], "Aug" => [], "Sep" => [], "Oct" => [], "Nov" => [], "Dec" => [], ];
			$unique_old_logins = TrackingCustomerActivity::whereRaw("( YEAR(date_time) = YEAR(CURDATE()) OR YEAR(date_time) = YEAR(CURDATE())-1 )")->get();
			
			// Step 2. Fill Month Arrays with customer ID's
			foreach($unique_old_logins as $k => $v) {
				
				$month_prefix = date('M', strtotime($v->date_time));
				$year = date('Y', strtotime($v->date_time));
				$prefix = $month_prefix . "_" . $year;
				$customer = $v->customer_id;
				
				if($year == $cur_year) {
					if(!in_array($customer, $unique_old_this_year_months_data[$month_prefix])) {
						$unique_old_this_year_months_data[$month_prefix][] = $customer;
					}
				} else {
					if(!in_array($customer, $unique_old_last_year_months_data[$month_prefix])) {
						$unique_old_last_year_months_data[$month_prefix][] = $customer;
					}
				}
				
			}
			
			// Step 3. Change Month Array Values to the count of customers in them instad
			foreach($unique_old_last_year_months_data as $k => $month) {
				$count = count($unique_old_last_year_months_data[$k]);
				$unique_old_last_year_months_data[$k] = $count;
				$old_app_growth[] = $count;
			}
			foreach($unique_old_this_year_months_data as $k => $month) {
				$count = count($unique_old_this_year_months_data[$k]);
				$unique_old_this_year_months_data[$k] = $count;
				$old_app_growth[] = $count;
			}
			
			// Step x. Test
			foreach($old_app_growth as $k => $v) {
				//echo $v . "\n";
			}
		
		
		
		$data = [
			"old_app_growth" => $old_app_growth,
			"new_app_growth" => $new_app_growth,
			"months" => $months,
		];
		
		$res = $today->set('app_engagement', $data);
	
		/*} catch(Exception $e) {
			
		}*/
		
		//$res = $today->set('this_years_new_app_engagement', $unique_new_this_year_months_data);
		
	}
	
	
	private function logSchemesAvg()
	{
		
		//$res = $today->set('engagement', $engagement);
		$today = $this->todaysEntry();
		$avgs = [];
		
		
		$schemes = Scheme::active();
		$start_date = date('Y-m-d', strtotime('6 days ago'));
		$end_date = date('Y-m-d');
		//$this->info("start: $start_date");
		//$this->info("end: $end_date");
		
		foreach($schemes as $s) {
			
			
			$customers = $s->customers;
			$customer_count = 0;
			$avg = 0.0;
			
			
			foreach($customers as $c) {
				$usage = $c->getUsage($start_date, $end_date, true);
				$count = $c->getUsage($start_date, $end_date, true)->count();
				if($count < 7)
					continue;
				$total_cod = $usage->sum('cost_of_day');
				$avg_cod = $total_cod;
				
				$customer_count++;
				$avg += $avg_cod;
			}
			
			if($customer_count <= 0)
				continue;
			
			
			$avg = $avg / $customer_count;
			$avgs[$s->scheme_number] = $avg;
			//$this->info("Avg for scheme " . $s->scheme_nickname . ": " . $avg);
			

			//$this->info("$entries entries in " . $s->scheme_nickname);
		}
		
		$res = $today->set('7day_avgs', $avgs);
	}
	
	
	private function logTopupMethods()
	{
		
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 0);
		
		$today = $this->todaysEntry();
		$topup_methods = [];
		
		$topups = PaymentStorage::whereRaw("YEAR(time_date) = '" . date("Y") . "'")
		->orderBy(DB::raw('time_date'))
		->get();
		
		foreach($topups as $k => $v) {
			
			$month = Carbon\Carbon::parse($v->time_date)->format('m');
			
			if(!isset($topup_methods[$month])) {
				$topup_methods[$month] = [
					"paypal" => 0,
					"stripe" => 0,
					"payzone" => 0,
					"paypoint" => 0,
				];
			}
			
			if(substr($v->ref_number, 0, 6) == "PAYID-" || substr($v->ref_number, 0, 4) == "PAY-" || $v->acceptor_name_location_ == "paypal") {
				$paymentType = "paypal";
				$topup_methods[$month][$paymentType]++;
				$topup_methods[$month]["date"] = Carbon\Carbon::parse($v->time_date)->format('Y-m');
			}
		
			if($v->acceptor_name_location_ == "paypoint" || substr($v->ref_number, 0, 3) == "PPR") {
				$paymentType = "paypoint";
				$topup_methods[$month][$paymentType]++;
				$topup_methods[$month]["date"] = Carbon\Carbon::parse($v->time_date)->format('Y-m');
			}
			
			if($v->acceptor_name_location_ == "payzone" || substr($v->ref_number, 0, 3) == "PZ-") {
				$paymentType = "payzone";
				$topup_methods[$month][$paymentType]++;
				$topup_methods[$month]["date"] = Carbon\Carbon::parse($v->time_date)->format('Y-m');
			}
			
			if($v->acceptor_name_location_ == "stripe" || substr($v->ref_number, 0, 3) == "ch_") {
				$paymentType = "stripe";
				$topup_methods[$month][$paymentType]++;
				$topup_methods[$month]["date"] = Carbon\Carbon::parse($v->time_date)->format('Y-m');
			}

		}
		
		
		//$this->info($topup_methods["04"]["paypal"]);

		$res = $today->set('year_topup_methods', $topup_methods);
		
		$this->info("logTopupMethods: " . serialize($res));
		
		
	}
	
	private function logSchemeUptime()
	{
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 0);
		
		$today = $this->todaysEntry();
		
		$week = DataSet::getLastWeek();
		$thisWeek = $week['this_weeks_days']; 
		$lastWeek = $week['last_weeks_days']; 
		
		$schemeUptimes = [];
		
		foreach($thisWeek as $k=>$v) {
			
			$date = $v;
			
			$trackingSchemes = TrackingScheme::where('date', $date)->get();
			foreach($trackingSchemes as $y => $track) {
				$scheme = Scheme::find($track->scheme_number);
				if($scheme->id == 23 || $scheme->id == 15 || $scheme->id == 6)
					continue;
				if($scheme) {
					if(!isset($schemeUptimes[$scheme->scheme_nickname]))
					{
						$schemeUptimes[$scheme->scheme_nickname][] = $track->offline_times;
					} else {
						$schemeUptimes[$scheme->scheme_nickname][] = $track->offline_times;
					}
				}
			}		
			
		}
		
		
		$res = $today->set('scheme_week_uptime', $schemeUptimes);
		
		$this->info("logSchemeUptime: " . serialize($res));
		
	}
	
	private function logFaqs()
	{
		
		$today = $this->todaysEntry();
		$faq_data = [];
		$faqs = TrackingFaqClick::select(['title', 'clicks'])->groupBy('title')->get();
		
		
		foreach($faqs as $k => $v) {			
			$most_popular_clicks_for_title = TrackingFaqClick::where('title', $v->title)->orderBy('clicks', 'DESC')->first();
			
			//echo "Most popular: '" . $v->title . "': " . $most_popular_clicks_for_title->clicks . " (" . $most_popular_clicks_for_title->id . ") \n";
			$faq_data['popular_faqs'][] = $most_popular_clicks_for_title;
		}
		
		usort($faq_data['popular_faqs'], function($a, $b) {return ($a->clicks <  $b->clicks); });
		
		foreach($faq_data['popular_faqs'] as $k => $v) {
			//echo "Most popular: '" . $v->title . "': " . $v->clicks . " \n";
		}
		
		$res = $today->set('faq_data', $faq_data);
		$this->info("logFaqs: " . serialize($res));
		
	}
	
	private function logMisc()
	{
		ini_set('memory_limit', '-1');
		
		$today = $this->todaysEntry();
		$misc_data = [];
		$statements_issued = [];
		$this_month = date('Y-m');
		$last_month = date('Y-m', strtotime('-1 month'));
		
		$statements_issued_this_month = SnugzoneAppStatement::whereRaw("(created_at LIKE '%$this_month%')")->count();
		$statements_issued_last_month = SnugzoneAppStatement::whereRaw("(created_at LIKE '%$last_month%')")->count();
		
		$autotopup_this_month = StripeCustomerSubscription::whereRaw("(created_at LIKE '%$this_month%')")->count();
		$autotopup_last_month = StripeCustomerSubscription::whereRaw("(created_at LIKE '%$last_month%')")->count();
		$autotopup_subscriptions_this_month = StripeCustomerSubPayment::whereRaw("(status = '1' AND amount <= '5' AND created_at LIKE '%$this_month%')")->count();
		$autotopup_subscriptions_last_month = StripeCustomerSubPayment::whereRaw("(status = '1' AND amount <= '5' AND created_at LIKE '%$last_month%')")->count();
		
		
		$closed_accounts_this_month = DB::table('customers')->whereRaw("(deleted_at LIKE '%$this_month%')")->count();
		$closed_accounts_last_month = DB::table('customers')->whereRaw("(deleted_at LIKE '%$last_month%')")->count();
		$opened_accounts_this_month = DB::table('customers')->whereRaw("(commencement_date LIKE '%$this_month%')")->count();
		$opened_accounts_last_month = DB::table('customers')->whereRaw("(commencement_date LIKE '%$last_month%')")->count();
		
		$autotopup_earnings_last_month = 0;
		$autotopup_earnings_this_month = 0;
		$autotopup_invoices_last_month = 0;
		$autotopup_invoices_this_month = 0;
			
		try {
			
			$stripe_last_month = StripeCustomerSubscription::APIInvoiceTotal($last_month, $last_month);
			$stripe_this_month = StripeCustomerSubscription::APIInvoiceTotal($this_month, $this_month);
			$autotopup_earnings_last_month = $stripe_last_month->amount;
			$autotopup_earnings_this_month = $stripe_this_month->amount;
			$autotopup_invoices_last_month = $stripe_last_month->count;
			$autotopup_invoices_this_month = $stripe_this_month->count;
			
		} catch(Exception $e) {
			
			echo $e->getMessage();
		}
		
		//die();
		
		
		$misc_data['statements_this_month'] = $statements_issued_this_month;
		$misc_data['statements_last_month'] = $statements_issued_last_month;
		$misc_data['autotopup_this_month'] = $autotopup_this_month;
		$misc_data['autotopup_last_month'] = $autotopup_last_month;
		$misc_data['autotopup_subscriptions_this_month'] = $autotopup_subscriptions_this_month;
		$misc_data['autotopup_subscriptions_last_month'] = $autotopup_subscriptions_last_month;
		$misc_data['autotopup_earnings_last_month'] = $autotopup_earnings_last_month;
		$misc_data['autotopup_earnings_this_month'] = $autotopup_earnings_this_month;
		$misc_data['closed_accounts_this_month'] = $closed_accounts_this_month;
		$misc_data['closed_accounts_last_month'] = $closed_accounts_last_month;
		$misc_data['opened_accounts_this_month'] = $opened_accounts_this_month;
		$misc_data['opened_accounts_last_month'] = $opened_accounts_last_month;
		
		$res = $today->set('misc_data', $misc_data);
		$this->info("logMisc: " . serialize($res));
		
	}
	
	
	private function logTopupTrends()
	{
		ini_set('memory_limit', '-1');
		
		$today = $this->todaysEntry();
		$topup_trends_data = [];
		
		$this_year = date('Y', strtotime('this year'));
		$this_year_last_day = date('Y-m-d');
		$t_from = $this_year . '-01-01';
		$t_to = $this_year_last_day;
		$t_from_new_app = $this_year . '-10-01';
		
		$last_year = date('Y', strtotime('last year'));
		$last_year_last_day = str_replace($this_year, $last_year, date('Y-m-d'));
		$l_from = $last_year . '-01-01';
		$l_to = $last_year_last_day;
		
		$start = date('Y-m', strtotime($l_from));
		$end = date('Y-m', strtotime($t_to));
		$customer_increase = [];
		$topup_100_increase = [];
		$months = [];
		
		//echo "$start - $end \n";
		while(Carbon\Carbon::parse($start) <= Carbon\Carbon::parse($end)) {

			$customer_increase[] = count(System::getCustomersDate($start . "-31"));		
			$topup_100_count = PaymentStorage::whereRaw("(settlement_date LIKE '%$start%' AND amount = '100')")->count();
			$topup_100_increase[] = $topup_100_count;
			$month = date('M Y', strtotime($start));
		
			$months[] = "'$month'";
			
			//echo $start . ": " . $topup_100_count . "\n";
			
			$start = new DateTime($start);
			$start = $start->modify(' +1 month');
			$start = $start->format('Y-m-d');
			
			$start = date('Y-m', strtotime($start));
			
		}
		
		$topup_trends_data['customer_increase'] = $customer_increase;
		$topup_trends_data['100_topup_increase'] = $topup_100_increase;
		$topup_trends_data['months'] = $months;
	
	
		$res = $today->set('topup_trends_data', $topup_trends_data);
		$this->info("logTopupTrends: " . serialize($res));
		
	}
	
	private function todaysEntry($force_make = true, $date = null)
	{
		
		if($date == null)
			$date = date('Y-m-d');
		
		$todays_entry = SystemGraphData::where('date', $date)->first();
		
		if(!$todays_entry)
		{
			if($force_make) {
				$todays_entry = new SystemGraphData();
				$todays_entry->date = $date;
				$todays_entry->data = "";
				$todays_entry->created_at = date('Y-m-d H:i:s');
				$todays_entry->updated_at = date('Y-m-d H:i:s');
				$todays_entry->save();
			}
		}
		//
		return $todays_entry;
	}

	private function yesterdaysEntry()
	{
		
		$yesterdays_entry = SystemGraphData::where('date', date('Y-m-d', strtotime('-1 days')))->first();
		
		//
		return $yesterdays_entry;
	}
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
