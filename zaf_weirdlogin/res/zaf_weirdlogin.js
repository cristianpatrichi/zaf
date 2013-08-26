$(document).ready(function(){
	$('a#emailLink').click(function(e){
		e.preventDefault();
		
		params = 'tx_zafweirdlogin_pi1[email]='+$('#email').val()+'&no_cache=1';
		$.ajax({type: "GET", url:"index.php?id=43", 
			data: params ,
			success: function(msg){
				success(msg);				
			},
			error: function(){failedRequest();}
		});
		
		function success(msg) {
			obj = JSON.parse(msg);
			
			if(obj.err == 'err') {				
				$('.response').hide();
				$('.response').html(obj.msg);				
				$('.response').stop().fadeIn('slow');
			}
			else {
				$('.response').hide();
				$('.response').html(obj.msg);
				$('.theForm').stop().fadeOut('slow');
				$('.response').stop().fadeIn('slow');
			}
			
		}
		
		function failedRequest(){
		}
	});
	
	
	//
});