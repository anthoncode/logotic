

function all() 
{
	// Ajax config
	$.ajax({
        type: "GET", //we are using GET method to get all record from the server
        url: 'all.php', // get the route value
        success: function (response) {//once the request successfully process to the server side it will return result here
            
            // Parse the json result
        	response = JSON.parse(response);

            var html = "";
            // Check if there is available records
            if(response.length) {
            	html += '<div class="list-group">';
	            // Loop the parsed JSON
	            $.each(response, function(key,value) {
	            	// Our employee list template
					html += '<a href="#" class="list-group-item list-group-item-action">';
					html += "<p>" + value.first_name +' '+ value.last_name + " <span class='list-email'>(" + value.email + ")</span>" + "</p>";
					html += "<p class='list-address'>" + value.address + "</p>";
					html += "<button class='btn btn-sm btn-primary mt-2' data-toggle='modal' data-target='#edit-employee-modal' data-id='"+value.id+"'>Edit</button>";
					html += "<button class='btn btn-sm btn-danger mt-2 ml-2 btn-delete-file' data-id='"+value.id+"' typle='button'>Delete</button>";
					html += '</a>';
	            });
	            html += '</div>';
            } else {
            	html += '<div class="alert alert-warning">';
				  html += 'No records found!';
				html += '</div>';
            }

            

            // Insert the HTML Template and display all employee records
			$("#employees-list").html(html);
        }
    });
}

function resetForm(selector) 
{
	$(selector)[0].reset();
}


function get() 
{
	$(document).delegate("[data-target='#edit-employee-modal']", "click", function() {

		var employeeId = $(this).attr('data-id');

		// Ajax config
		$.ajax({
	        type: "GET", //we are using GET method to get data from server side
	        url: 'get.php', // get the route value
	        data: {employee_id:employeeId}, //set data
	        beforeSend: function () {//We add this before send to disable the button once we submit it so that we prevent the multiple click
	            
	        },
	        success: function (response) {//once the request successfully process to the server side it will return result here
	            response = JSON.parse(response);
	            $("#edit-form [name=\"id\"]").val(response.id);
	            $("#edit-form [name=\"email\"]").val(response.email);
	            $("#edit-form [name=\"first_name\"]").val(response.first_name);
	            $("#edit-form [name=\"last_name\"]").val(response.last_name);
	            $("#edit-form [name=\"address\"]").val(response.address);
	        }
	    });
	});
}


function del() 
{
	$(document).delegate(".btn-delete-file", "click", function() {
		
		Swal.fire({
			icon: 'warning',
		  	title: 'Are you sure you want to delete this record?',
		  	showDenyButton: false,
		  	showCancelButton: true,
		  	confirmButtonText: 'Yes'
		}).then((result) => {
		  /* Read more about isConfirmed, isDenied below */
		  if (result.isConfirmed) {

		  	var idFile = $(this).attr('data-id');

		  	// Ajax config
			$.ajax({
		        type: "GET", //we are using GET method to get data from server side
		        url: './remove-product.php?action=remove', // get the route value
		        data: {id:idFile}, //set data
		        beforeSend: function () {//We add this before send to disable the button once we submit it so that we prevent the multiple click
		            
		        },
		        success: function (response) {//once the request successfully process to the server side it will return result here
		            // Reload lists of employees
	            	//all();

		            Swal.fire('Success.', response, 'success');
		            location.reload();
		        }
		    });

		    
		  } else if (result.isDenied) {
		    Swal.fire('Changes are not saved', '', 'info')
		  }
		});

		
	});
}


$(document).ready(function() {

	// Get all employee records
	//all();



	// Get the data and view to modal
	get();
	 

	// Delete record via ajax
	del();
});