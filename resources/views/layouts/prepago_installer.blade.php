<!DOCTYPE html>
<html lang="en">
   <head>
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
      <title>Prepago Data Logger</title>
	    {!! HTML::style('resources/css/bootstrap.min.css') !!} 
	     {!! HTML::style('resources/css/bootstrap-responsive.min.css') !!}
      {!! HTML::style('resources/data_logger/css/reset.css') !!}
      {!! HTML::style('resources/data_logger/css/glyphs.css') !!}
      {!! HTML::style('resources/data_logger/css/stylesheet.css') !!}
      {!! HTML::style('resources/data_logger/css/media.css') !!}
      {!! HTML::style('https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css') !!}
      {!! HTML::script('resources/data_logger/js/jquery-1.11.0.min.js') !!}
      <script src="https://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
      {!! HTML::script('resources/data_logger/js/general.js') !!}
      {!! HTML::script('resources/js/jquery.tablesorter.min.js') !!}
	    {!! HTML::script('resources/js/bootstrap-transition.js') !!}
    {!! HTML::script('resources/js/bootstrap-alert.js') !!}
    {!! HTML::script('resources/js/bootstrap-modal.js') !!}
    {!! HTML::script('resources/js/bootstrap-dropdown.js') !!}
    {!! HTML::script('resources/js/bootstrap-scrollspy.js') !!}
    {!! HTML::script('resources/js/bootstrap-tab.js') !!}
    {!! HTML::script('resources/js/bootstrap-tooltip.js') !!}
    {!! HTML::script('resources/js/bootstrap-popover.js') !!}
    {!! HTML::script('resources/js/bootstrap-button.js') !!}
    {!! HTML::script('resources/js/bootstrap-collapse.js') !!}
    {!! HTML::script('resources/js/bootstrap-carousel.js') !!}
    {!! HTML::script('resources/js/bootstrap-typeahead.js') !!}
    {!! HTML::script('resources/js/bootstrap-datetimepicker.min.js') !!}
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.9/css/all.css" integrity="sha384-5SOiIsAziJl6AWe0HWRKTXlfcSHKmYV4RBF18PPJ173Kzn7jzMyFuTtk8JA7QQG1" crossorigin="anonymous">
      {!! HTML::style('resources/css/fontawesome-all.css') !!}
   </head>
   <body>
      <div class="header">
         <div class="container">
            @if( Auth::user() )
            <div class="nav">
               <ul>
                  <style>
                     .glyphicon {
						position: relative;
						top: 0px;
						display: inline-block;
						font-family: 'Glyphicons Halflings';
						font-style: normal;
						font-weight: normal;
						line-height: 0;
						font-size: 1.32em;
						-webkit-font-smoothing: antialiased;
                     }
					 
                     #installer_nav {
						/*background: #ccc;*/
						padding-top: 2%;
						font-size: 2.5vh;
                     }
					 
                     #installer_nav ul{
						list-style-type: none;
                     }
					 
                     #installer_nav ul li {
						display: inline;
						margin-left: 5%;
						text-align: center;
						color: black;
						/*background: black;*/
                     }
					 
                     @media only screen and (max-width: 600px) {
						
						#installer_nav ul li {
							display: block;
							/*background: #ccc;*/
							margin: 0px auto 4.5% auto;
							border-radius: 3px;
						 }
						 
                     }
      
                  </style>
                  <!-- <li><a href="{!! URL::to('prepago_installer/add-units?type=ev') !!}" title="Add EV Unit"><div class="ico_plus_red small"></div></a></li> -->
                  <!-- <li><a href="{!! URL::to('prepago_installer/tools') !!}" title="Tools"><div class="ico_tools small"></div></a></li> -->
                  <!-- <li><a href="{!! URL::to('prepago_installer/help') !!}" title="Help"><div class="ico_help small"></div></a></li> -->
                  @if (Auth::user()->schemes && Auth::user()->schemes()->count() > 1)
                  <li class="active"><a href="{!! URL::to('welcome-schemes') !!}"><i style="color: black;" class="fa fa-2x fa-list-alt"></i></a></li>
                  @endif
                  <li><a href="{!! URL::to('logout') !!}" title="Sign Out"><i style="color: black;" class="fa fa-2x fa-sign-in-alt"></i></a></li>
               </ul>
            </div>
            @endif
            <a href="{!! URL::to('prepago_installer') !!}">
               <div class="logo long"></div>
            </a>
            <div class="clear"></div>
         </div>
      </div>
      <div id="installer_nav">
         <ul>
		 
			<a href="{!! URL::to('prepago_installer') !!}" title="Dashboard">
               <li>Dashboard</li>
            </a>
            <a href="{!! URL::to('prepago_installer/add-units') !!}" title="Add Unit">
               <li>Add Unit</li>
            </a>
            <a href="{!! URL::to('prepago_installer/address_translations') !!}" title="Manage addresses">
               <li>Manage addresses</li>
            </a>
			
			<!--
            <a href="{!! URL::to('prepago_installer/access_control') !!}" title="Access Control">
               <li>Access Control</li>
            </a>
			-->
			
         </ul>
      </div>
      <div class="content">
         <div class="container">
            {!! $page !!}
         </div>
      </div>
      <div class="footer"></div>
   </body>
</html>