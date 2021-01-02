
<div id="missing-apartment" class="modal fade" role="dialog">
	  <div class="modal-dialog">
	  
	  <!-- Modal content-->
	  <div class="modal-content">
		
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Missing apartment</h4>
	  </div>
	
	  <div class="modal-body">
	
	  <span>Please use this feature with <b>caution!</b> Only use with apartments you are sure <b>have not been set up.</b></span><br/><br/>
	  <hr/>

			<table width="100%">
			
				<tr>
					<td>
						<b>Apartment name to search for</b>
					</td>
				</tr>
				<tr>
					<td style='vertical-align:top;' width="20%">
						<input type="text" id="apartment_name" placeholder="Apartment name">
					</td>
					<td style='vertical-align:top;' width="50%">
						<button type="button" class="btn btn-primary" id="apartment_search">Search & Add</button>
					</td>
				</tr>			
			</table>
			
			<hr/>
			
			<div class="well">
				<table id="search-results" width="100%">
					<tr> <td> <b> Search Results </b> </td> </tr>
					
				</table>
			</div>
			
	  </div>
	  
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	  </div>
	  
	  
	</div>

	</div>
	</div>
<script>
	$(function(){
		
		
		$('#apartment_search').on('click', function(){
			
			var apartment_t = $('#apartment_name');
			var apartment = apartment_t.val();
			
			searchForApartment(apartment);
			
		});
		
		function searchForApartment(apartment)
		{
			var search_results = $('#search-results');
			var apt_list = $('#prepop');
			
			$.ajax({
				
				
				url: '/open_account/search_apt',
				data: {apartment: apartment},
				type: 'POST',
				success: function(data){
					
					if(data.toLowerCase().indexOf('success') != -1) {
						
						var parts = data.split('|');
						var apt = parts[0];
						var meter = parts[1];
						var full_name = parts[2];
						
						
						var append = "<tr> <td> <b> Search Results </b> </td> </tr><tr>";
						append += "<td>";
						append += "Successfully added " + apt + " to the list!";
						append += "</td>";
						append += "</tr>";
						
						search_results.html(append);
						console.log(data)
					
						if(apt_list.html().indexOf(full_name) == -1) {
							apt_list.append('<option id="' + apt + '" value="' + meter + '">' + full_name + '</option>');
						}
						else
						{
							search_results.html(apt + " is already in the list!");
						}
						
					}
					else 
					{
						search_results.html(append);
					}
					
				},
				
				
			});
			
			
		}
		
		
	});
</script>	