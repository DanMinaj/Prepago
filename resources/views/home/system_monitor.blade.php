</div>
<div><br/></div>
<h1>System monitor</h1>
<div class="admin2">
   @include('includes.notifications')
   <!--
      <div class="alert alert-info alert-block">
      Please be aware that some customers may be restored from this list, as a result of a top-up after their shut-off. Such customers are marked green.
      </div>
      -->
   <style>
      .big {
      font-size:13px;
      }
      .bold {
      font-weight:bold;
      }
      .success {
      color: #3fb103;
      }
      .error {
      color: #ee5f5b;
      }
      .warning {
      color: #fbb450;
      }
	  .green-circle{
	  width:200px;height:200px;background:#3fb10347;border-radius:500px;
	  margin-left: 30%;
	  }
	  .circle_number{
		position: relative;
		top: 28%;
		/* left: 86px; */
		text-align: center;
		color: white;
		font-size: 41px;
	  }
	  .circle_txt{
		position: relative;
		top: 29%;
		/* left: 28%; */
		color: white;
		text-align: center;
	  }
   </style>
   <table width="100%">
      <tr>
         <td width="20%">
            <span class="big bold">Monitoring Date: </span>
         </td>
         <td width="80%">
            <span class="big">
            {!! $date !!}
            </span>
         </td>
      </tr>
   </table>
   <hr/>
   <!-- Main row -->
   <table width="100%">
      <tr>
         <!-- Left column -->
         <td style="vertical-align:top;" width="50%">
            <table width="100%">
               <tr>
                  <td width="27%">
                     <span class="big bold">Standing charges</span>
                  </td>
                  <td width="30%">
                     <span class="big">
                     @if(count($uninserted_s) <= 0)
                     <span class='success'><b>All good</b> <i class="fa fa-check-circle success"></i></span>
                     @else
                     <span class='error'>
						<b>{!!count($uninserted_s)!!} customers affected</b> <i class="fa fa-frown error"></i>
						<a target="_blank" href="{!! URL::to('system_reports/missing_standing?option=All+Schemes') !!}"><i class="fa fa-external-link-alt"></i> Fix</a>
					</span>
                     @endif
                     </span>
                  </td>
               </tr>
               <tr>
                  <td colspan='2'>Detects if any customer has not been charged the standing usage fee.<br/><br/><br/></td>
               </tr>
               <tr>
                  <td width="27%">
                     <span class="big bold">Other charges</span>
                  </td>
                  <td width="30%">
                     <span class="big">
                     @if($other_charges <= 0)
                     <span class='success'><b>All good</b> <i class="fa fa-check-circle success"></i></span>
                     @else
                     <span class='error'><b>&euro;{!!$other_charges!!}</b> <i class="fa fa-frown error"></i></span>
                     @endif
                     </span>
                  </td>
               </tr>
               <tr>
                  <td colspan='2'>Detects if 'other_charges' exist within customer usage. 
				  There should not be any as of 01/04/2019 due to accuracy improvements for Billing Engine.<br/><br/><br/></td>
               </tr>
               <tr>
                  <td width="27%">
                     <span class="big bold">Inconsistent usage</span>
                  </td>
                  <td width="30%">
                     <span class="big">
                     @if($inconsistent_usage <= 0)
                     <span class='success'><b>All good</b> <i class="fa fa-check-circle success"></i></span>
                     @else
                     <span class='error'>
						<b>{!!$inconsistent_usage!!} customers affected</b> <i class="fa fa-frown error"></i> 
						<a target="_blank" href="{!! URL::to('system_reports/inconsistent_usage') !!}"><i class="fa fa-external-link-alt"></i> Fix</a>
					</span>
                     @endif
                     </span>
                  </td>
               </tr>
               <tr>
                  <td colspan='2'>Detects if any customers' end_day_reading - start_day_reading is not equivalent to their total_usage.<br/><br/></td>
               </tr>
               <tr>
                  <td width="27%">
                     <span class="big bold">Duplicate usage</span>
                  </td>
                  <td width="30%">
                     <span class="big">
                     @if($duplicate_dhu <= 0)
                     <span class='success'><b>All good</b> <i class="fa fa-check-circle success"></i></span>
                     @else
                     <span class='error'>
						<b>{!!$duplicate_dhu!!} customers affected</b> <i class="fa fa-frown error"></i>
						<a target="_blank" href="{!! URL::to('system_reports/duplicate_dhu') !!}"><i class="fa fa-external-link-alt"></i> Fix</a>
					 </span>
                     @endif
                     </span>
                  </td>
               </tr>
               <tr>
                  <td colspan='2'>Detects if any customers have duplicate district_heating_usage entries.<br/><br/><br/></td>
               </tr>
			   
			   <!--
               <tr>
                  <td width="27%">
                     <span class="big bold">Non-reading Schemes</span>
                  </td>
                  <td width="30%">
                     <span class="big">
                     @if($non_readings <= 0)
                     <span class='success'><b>All good</b> <i class="fa fa-check-circle success"></i></span>
                     @else
                     <span class='error'><b>{!!count($non_readings_schemes)!!} non-reading schemes<br/>
					 @foreach($non_readings_schemes as $key => $s) 
						@if(count($non_readings_schemes) == ($key + 1))
							{!! $s->scheme_nickname !!}
						@else
							{!! $s->scheme_nickname !!},
						@endif
					 @endforeach
					<br/></b> <i class="fa fa-frown error"></i></span>
                     @endif
                     </span>
                  </td>
               </tr>
               <tr>
                  <td colspan='2'>Detects if any schemes weren't read today<br/><br/><br/></td>
               </tr>
			   -->
			   
            </table>
         </td>
         <!-- Right column -->
         <td style="vertical-align:top;"  width="50%">
			
			<div style="@if($problems_found > 0)background:#ee5f5ba8;@endif" class="green-circle">
				<div class="circle_number">{!! $problems_found !!}</div>
				<div class="circle_txt">Major problems found</div>
			</div>
			
         </td>
      </tr>
   </table>
</div>