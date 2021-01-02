<?php
   $current_page = $_SERVER['REQUEST_URI'];
   if(strpos($current_page, 'customer_tabview') !== false)
   	$current_page = 'customer_tabview';
   
   ?>
@if(Scheme::find(Auth::user()->scheme_number))
<form id="support_form" action="#" method="POST">
   <div id="fix-an-issue" class="modal fade" role="dialog">
      <div class="modal-dialog">
         <!-- Modal content-->
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal">&times;</button>
               <h4 class="modal-title">Fix an issue</h4>
            </div>
            <div class="modal-body">
               @if($current_page == "customer_tabview")
               <table width="100%">
                  <tr>
                     <td>{!!$data['house_number_name']!!} {!!$data['street1']!!}</td>
                  </tr>
                  <tr>
                     <td>{!!$data['street2']!!}</td>
                  </tr>
                  <tr>
                     <td>{!!$data['county']!!}</td>
                  </tr>
                  <input type="hidden" name="customer_ID" value="{!!$data['id']!!}">
                  <input type="hidden" name="customer" value="{!!$data['username'] !!}">
               </table>
               @else
               <table>
                  <tr>
                     <td><b>Scheme: </b></td>
                     <td>
                        <input type='hidden' name='scheme_ID' value="{!! Auth::user()->scheme_number !!}">
                        {!! Scheme::find(Auth::user()->scheme_number)->company_name !!}
                     </td>
                  </tr>
                  <tr>
                     <td><b>Page: </b></td>
                     <td>
                        <input type='hidden' name='page' value="{!! $current_page !!}">
                        {!! $current_page !!}
                     </td>
                  </tr>
               </table>
               @endif
               <br/>
               <textarea id="example" name="issue" placeholder="Short description of issue" width='500px' maxlength="100"></textarea>
               <table width="100%">
                  <tr>
                     <td><b>Operator</b></td>
                  </tr>
                  <tr>
                     <td><input type="hidden" name="operator_ID" value="{!! Auth::user()->id !!}">
                        <input type="text" name="operator"  placeholder="Operator name" value="{!! Auth::user()->employee_name !!}">
                     </td>
                  </tr>
                  <tr>
                     <td><b>Operator email</b> (allow us to get back to you)</td>
                  </tr>
                  <tr>
                     <td><input type="email" name="operator_email" placeholder="Operator email (your email)" value="{!!Auth::user()->email_address!!}"></td>
                  </tr>
                  <tr>
                     <td colspan="2"><b>Subject</b></td>
                  </tr>
                  <tr>
                     <td><input type="text" name="issue_title" maxlength="50" placeholder="Issue title" value=""></td>
                  </tr>
                  <tr>
                     <td colspan="2"><b>Description of issue</b> (100 words max) </td>
                  </tr>
                  <tr>
                     <td></td>
                  </tr>
                  <tr>
                     <td><input type="checkbox" name="receive_email" checked="true">Receive a copy via email</td>
                  </tr>
                  <tr>
                     <td><input type="checkbox" name="save_email" checked="true">Save this email address</td>
                  </tr>
               </table>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-primary">Submit</button>
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
</form>
@endif