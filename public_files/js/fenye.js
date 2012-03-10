/*$(document).ready(function(){
    $('.fenye a').live('click',function(){
	$("#content").fadeOut('slow');
	var href = $(this).attr("href");
	href = href.replace('view','view_ajax');
	$.get(href,function(data, textStatus){
	    $("#content").html(data).fadeIn('slow');
    });
	return false;
});
    });*/

$(document).ready(function(){
    $('.fenye a').live('click',function(){
	$("#content").fadeOut('slow');
	var href = $(this).attr("href");
	href = href+'&ajax=ajax';
	$.get(href,function(data, textStatus){
	    $("#content").html(data).fadeIn('slow');
    });
	return false;
});
    });


/*$(document).ready(function() {
    $('.fenye a').click(function() {
	$('#content').fadeOut('slow');
	var href = $(this).attr('href');
	href = href.replace('view','view_ajax');
	$('#content').load(href);
	return false;
    });
});*/

