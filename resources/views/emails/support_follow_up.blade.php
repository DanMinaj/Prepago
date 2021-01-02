<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<div lang="en-us" style="width:100%!important;margin:0;padding:0">
   <div style="padding:10px;line-height:18px;font-family:'Lucida Grande',Verdana,Arial,sans-serif;font-size:12px;color:#444444">
      <p dir="ltr">Hi {!! $customer->first_name !!},</p>
      
	  
	  @if(!$reminder)
	  <p dir="ltr">We just wanted to follow up with you about your recent support ticket #{!! $query->id !!} to make sure it was solved.</p>
      <p dir="ltr">Could you spare a quick moment to let us know please?</p>
	  @else
		 <p dir="ltr">We recently emailed you asking if you could let us know if your issue was solved!</p>
      <p dir="ltr">Could you spare a quick moment to let us know please? If you don't have the time, feel free to ignore this.</p>
	  @endif
	  
      <p dir="ltr">
		<a href="https://www.snugzone.biz/support-feedback.html?cid={!! $customer->id !!}&tid={!! $query->id !!}&rid=1">
			<button style="margin-right:5px;padding: 7px; border-radius: 7px; background: #5687f5; border: 1px solid #29105f; color: white; font-weight: bold;">
				🙂 Yes it was
			</button>
		</a>
		<a href="https://www.snugzone.biz/support-feedback.html?cid={!! $customer->id !!}&tid={!! $query->id !!}&rid=0">
			<button style="padding: 7px; border-radius: 7px; background: #c54159; border: 1px solid #5f1012; color: white; font-weight: bold;">
				☹️ No it wasn't
			</button>
		</a>
	  </p>
      <!--<p dir="ltr">If you don't wish to respond, feel free to ignore this. We'll close the ticket out automatically in 48 hours.</p>-->
      <p dir="ltr"></p>
      <div style="margin-top:25px">
         <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
            <tbody>
               <tr>
                  <td width="100%" style="padding:15px 0;border-top:1px dotted #c5c5c5">
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed" role="presentation">
                        <tbody>
                           <tr>
                              <td width="100%" style="padding:0;margin:0" valign="top">
                                 <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:15px;line-height:18px;margin-bottom:0;margin-top:0;padding:0;color:#1b1d1e" dir="ltr">                                                                    <strong>{!! $customer->first_name !!} {!! $customer->surname !!} - {!! $customer->username!!} </strong> (SnugZone App)                                                            </p>
                                 <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:13px;line-height:25px;margin-bottom:15px;margin-top:0;padding:0;color:#bbbbbb" dir="ltr">              {!! (new DateTime($query->created_at))->format('F j, Y, g:i a') !!}            </p>
                                 <div dir="auto" style="color:#2b2e2f;font-family:'Lucida Sans Unicode','Lucida Grande','Tahoma',Verdana,sans-serif;font-size:14px;line-height:22px;margin:15px 0">
								 <h3> Your issue: </h3>
								 {!! nl2br($query->getIssueFiltered()) !!}
                                 </div>
                                 <p dir="ltr">                                  </p>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </td>
               </tr>
            </tbody>
         </table>
         <p dir="ltr"></p>
		 @if(strlen($query->getResponseFiltered()) > 1)
		 <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
            <tbody>
               <tr>
                  <td width="100%" style="padding:15px 0;border-top:1px dotted #c5c5c5">
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed" role="presentation">
                        <tbody>
                           <tr>
                              <td width="100%" style="padding:0;margin:0" valign="top">
                                 <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:15px;line-height:18px;margin-bottom:0;margin-top:0;padding:0;color:#1b1d1e" dir="ltr">                                                                    <strong>SnugZone Support </strong>                                                           </p>
                                 <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:13px;line-height:25px;margin-bottom:15px;margin-top:0;padding:0;color:#bbbbbb" dir="ltr">              {!! (new DateTime($query->created_at))->format('F j, Y, g:i a') !!}            </p>
                                 <div dir="auto" style="color:#2b2e2f;font-family:'Lucida Sans Unicode','Lucida Grande','Tahoma',Verdana,sans-serif;font-size:14px;line-height:22px;margin:15px 0">
								 <h3> Our response: </h3>
								 {!! nl2br($query->getResponseFiltered()) !!}
                                 </div>
                                 <p dir="ltr">                                  </p>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </td>
               </tr>
            </tbody>
         </table>
		 @endif
		 
		 @if(count($query->getSMSResponses()) > 0)
			 @foreach($query->getSMSResponses() as $k => $v)
				<p dir="ltr"></p>
				 <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
					<tbody>
					   <tr>
						  <td width="100%" style="padding:15px 0;border-top:1px dotted #c5c5c5">
							 <table width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed" role="presentation">
								<tbody>
								   <tr>
									  <td width="100%" style="padding:0;margin:0" valign="top">
										 <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:15px;line-height:18px;margin-bottom:0;margin-top:0;padding:0;color:#1b1d1e" dir="ltr">                                                                    <strong>SnugZone Support </strong>                                                           </p>
										 <p style="font-family:'Lucida Grande','Lucida Sans Unicode','Lucida Sans',Verdana,Tahoma,sans-serif;font-size:13px;line-height:25px;margin-bottom:15px;margin-top:0;padding:0;color:#bbbbbb" dir="ltr">              {!! (new DateTime($v->date_time))->format('F j, Y, g:i a') !!}            </p>
										 <div dir="auto" style="color:#2b2e2f;font-family:'Lucida Sans Unicode','Lucida Grande','Tahoma',Verdana,sans-serif;font-size:14px;line-height:22px;margin:15px 0">
										  <h3> Our response: </h3>
										 {!! nl2br($v->message) !!}
										 </div>
										 <p dir="ltr">                                  </p>
									  </td>
								   </tr>
								</tbody>
							 </table>
						  </td>
					   </tr>
					</tbody>
				 </table>
			 @endforeach
		 @endif
      </div>
      <div style="color:#aaaaaa;margin:10px 0 14px 0;padding-top:10px;border-top:1px solid #eeeeee">
         This email is a service from SnugZone Support.
		 Delivered by <a href="https://mail.google.com/" style="color:black" target="_blank" data-saferedirecturl="">Gmail</a> | <a href="http://prepago.ie/privacy.php" style="color:black" target="_blank" data-saferedirecturl="http://prepago.ie/privacy.php"> Privacy Policy </a>
      </div>
   </div>
   <span style="color:#ffffff" aria-hidden="true">[OLKD7P-6WQ9]</span>
   <div class="yj6qo"></div>
   <div style="display:none" class="adL">
      <div>          </div>
   </div>
   <div class="adL">
   </div>
</div>
	</body>
</html>
