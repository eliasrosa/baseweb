$(function(){
	var user = $('.login input.user');
	var pass = $('.login input.pass');
	
	if(user.val() != "")
		pass.focus();
	else
		user.focus();
});
