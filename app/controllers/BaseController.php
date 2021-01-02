<?php

class BaseController extends Controller {

	protected $bossLevel;
	protected $currencySign;
	protected $currentScheme;
	

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
		
		//set currency sign
		if (Auth::check())
		{
			$this->currencySign = Auth::user()->scheme_number ? Scheme::where('scheme_number', Auth::user()->scheme_number)->first()->currency_sign : '£';
			$this->currentScheme = User::find(Auth::user()->id)->group_id;
			$this->bossLevel = getBossLevel(Auth::user());
			View::share('currencySign', $this->currencySign);
			View::share('currentScheme', $this->currentScheme);
			View::share('bossLevel', $this->bossLevel);
			
		} else {
			
		}

		Form::macro('customradio', function($name, $value, $text, $checked = false){
			$ischecked = $checked?'checked="checked"':'';
		    return '<label><input name="'.$name.'" value="'.$value.'" type="radio" '.$ischecked.'>'.$text.'</label>';
		});

		Form::macro('customcheckbox', function($name, $value, $text, $checked = false){
			$ischecked = $checked?'checked="checked"':'';
		    return '<label><input name="'.$name.'" value="'.$value.'" type="checkbox" '.$ischecked.'>'.$text.'</label>';
		});
	}

}