/* <![CDATA[ */
jQuery(function(){
	jQuery("#username").validate({
		expression: "if (VAL.length >= 2 && VAL) return true; else return false;",
		message: "Invalid username"
	});
	jQuery("#password").validate({
			expression: "if (VAL.length >= 4 && VAL) return true; else return false;",
			message: "Invalid password"
		});
	jQuery("#password2").validate({
		expression: "if ((VAL == jQuery('#password').val()) && VAL) return true; else return false;",
		message: "Password doesn't match"
	});
	jQuery("#email").validate({
		expression: "if (VAL.match(/^[^\\W][a-zA-Z0-9\\_\\-\\.]+([a-zA-Z0-9\\_\\-\\.]+)*\\@[a-zA-Z0-9_]+(\\.[a-zA-Z0-9_]+)*\\.[a-zA-Z]{2,4}$/)) return true; else return false;",
		message: "Invalid email"
	});
	jQuery("#sq_answer").validate({
		expression: "if (VAL.length >= 2 && VAL) return true; else return false;",
		message: "Required field"
	});
	jQuery("#path").validate({
		expression: "if (VAL.length >= 2 && VAL) return true; else return false;",
		message: "Required field"
	});
	jQuery("#code").validate({
		expression: "if (VAL.length >= 2 && VAL) return true; else return false;",
		message: "Required field"
	});
	jQuery("#title").validate({
		expression: "if (VAL.length >= 2 && VAL) return true; else return false;",
		message: "Required field"
	});
	jQuery("#desc").validate({
		expression: "if (VAL.length >= 2 && VAL) return true; else return false;",
		message: "Required field"
	});
});
/* ]]> */