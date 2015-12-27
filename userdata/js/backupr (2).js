//$("#menu_group_main li a").live('click', function() {
//	//$("#menu_group_main li a").removeClass('current');
//	$(this).addClass('current');
//	$("#portlets").html('Fetching data...');
//	switch($(this).parent().attr('id')){
//		case 'myTweets':
//			myTweets();
//			break;
//	}
//	return false;
//});

$(".edit_icon").live('click',function(){
	$.post('showMails.php', {'id': $(this).attr('id')}, function(data) {
		$(data).modal({
			minHeight:500,
			minWidth: 800
		});
	});
});

$("#searchWord").live('keydown', function() {
	if($(this).val() != '') {
		$("#searchResultContainer").html('Searching...');
		$.post('instantSearch.php', {'text': $(this).val()}, function(data) {
			$("#showUpdatedMails").html(data);
		});
	} else {
		$("#showUpdatedMails").html('');
	}
});

$(".showUserMail").live('click', function() {
	$.post('showUserMail.php', {'username': $(this).attr('id')}, function(data) {
		$("#showUpdatedMails").html(data);
		$("#mailsTable").tablesorter();
	});	
});

function showMails(username) {
    $.post('showMails.php', {'username': username}, function(data) {
        $("#showUpdatedMails").html(data);
	$("#mailsTable").tablesorter();
    });
}

function myTweets() {
	$.post('showTweets.php', function(data) {
	        $("#portlets").html(data);
	});
}

$(".updateUsersAccount").live('click', function() {
	$("#modalFormForUpdation").modal();
});




$("#updateUsersGmailAccount").live('click',function() {
	var gmailUsername = $("#usersGmailId").val();
	var gmailPassword = $("#usersGmailPassword").val();
	$(this).val('Updating...');
	//Connect to gmail here and update the current page.
});

$(document).ready(function() {
	if($("#emailOfUserName").length) {
		$(".userNameOfLoggedInUser").text($("#emailOfUserName").text());
		showMails($("#emailOfUserName").text());
	} else {
		var validator = $("#signupform").validate({
			rules: {
				firstname: "required",
				lastname: "required",
				gender: "required",
				username: {
					required: true,
					minlength: 2,
					remote: "users.php"
				},
				password: {
					required: true,
					minlength: 5
				},
				password_confirm: {
					required: true,
					minlength: 5,
					equalTo: "#password"
				},
				email: {
					required: true,
					email: true,
					remote: "emails.php"
				},
				dateformat: "required",
				terms: "required"
			},
			messages: {
				firstname: "Enter your firstname",
				lastname: "Enter your lastname",
				gender: "Please select your gender",
				username: {
					required: "Enter a username",
					minlength: jQuery.format("Enter at least {0} characters"),
					remote: jQuery.format("{0} is already in use")
				},
				password: {
					required: "Provide a password",
					rangelength: jQuery.format("Enter at least {0} characters")
				},
				password_confirm: {
					required: "Repeat your password",
					minlength: jQuery.format("Enter at least {0} characters"),
					equalTo: "Enter the same password as above"
				},
				email: {
					required: "Please enter a valid email address",
					minlength: "Please enter a valid email address",
					remote: jQuery.format("{0} is already in use")
				},
				dateformat: "Choose your preferred dateformat",
				terms: " "
			},
			// the errorPlacement has to take the table layout into account
			errorPlacement: function(error, element) {
				if ( element.is(":radio") )
					error.appendTo( element.parent().next().next() );
				else if ( element.is(":checkbox") )
					error.appendTo ( element.next() );
				else
					error.appendTo( element.parent().next() );
			},
			// specifying a submitHandler prevents the default submit, good for the demo
			submitHandler: function(form) {
				form.submit();
				/*$(".contentBox").remove();
				$("#dashboardMainContainer").removeClass('hidden');
				setInterval("showMails()", 5000);*/
			},
			// set this class to error-labels to indicate valid fields
			success: function(label) {
				// set &nbsp; as text for IE
				label.html("&nbsp;").addClass("checked");
			}
		});
		
		// propose username by combining first- and lastname
		$("#username").focus(function() {
			var firstname = $("#firstname").val();
			var lastname = $("#lastname").val();
			if(firstname && lastname && !this.value) {
				this.value = firstname + "." + lastname;
			}
		});
	}
});