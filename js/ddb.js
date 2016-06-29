function dthook(){

	var df = $('input[type=date]');

	df.each(function(){ 
		var d = $(this).val();  
		$(this).daterangepicker({ 
			autoUpdateInput: false,
			startDate: d, 
			endDate: d, 
			locale: { format: "YYYY-MM-DD HH:mm:ss" }, 
			singleDatePicker: true, 
			timePicker: true, 
			timePickerIncrement: 30, 
			timePicker24Hour: true,
			todayBtn: 'linked'
		}); 
	});

	df.on('apply.daterangepicker', function(ev, picker) {
	    $(this).val( picker.startDate.format('YYYY-MM-DD HH:mm:ss') );
	});

	df.on('cancel.daterangepicker', function(ev, picker) {
	    $(this).val('');
	});

}

function modal( url, title ){

	$("#modal-content").html("Loading...");
	$("#modal").show();
	$("#modalbg").show();
	$("body").addClass("modal-open");
	$("#modal-content").load( url , function(){
		$("#modal-title").html( title );
		$("#modalbg").click(function(){ 
			$("#modal").hide(); 
			$("#modalbg").hide();
			$("body").removeClass("modal-open");
		});
	}, function(){ alert("error"); });

}

function editDevice(id){
	$("#modal-content").html("Loading...");
	$("#modal").show();
	$("#modalbg").show();
	$("body").addClass("modal-open");
	$("#modal-content").load("data.php?edit=" + id + "&t=" + Date.now(), function(){
		$("#modal-title").html("Edit device");
		$("#modalbg").click(function(){ 
			$("#modal").hide(); 
			$("#modalbg").hide();
			$("body").removeClass("modal-open");
		});
	}, function(){ alert("error"); });
}

function addextra(){
	$("<br><input class='form-control' type='text' name='extra[]'>").appendTo("#extra_container");
}

function editform(f){
	$.post( $("#form_edit").attr("action"), $("#form_edit").serialize(), function(data){
		if(data == "ok"){
			$("#modal-content").html("Loading...");
			$(".content").load( location.href + " .content > *", function(){ 
				$("#modal").hide();
				$("#modalbg").hide();
				$("body").removeClass("modal-open");
			});
		}else{
			alert(data);
		}
	});
}

var s_results = 0;
var s_search = "";
function updateTitle(){
	var t = "DDB";
	if(s_search != "") t += " | Search: " + s_search + " - " + s_results + " results.";
	document.title = t
	$("#title").html(t);
}

$(function(){

	dthook(); // hook date pickers

	updateTitle(); // set title

	$(".content").on("click", "img[data-preview]", function(){

		$("#modal-title").html("Image preview");
		$("#modal-content").html("<img src='" + $(this).attr('data-preview') + "'>");
		$("body").addClass("modal-open");

		$("#modal").show();
		$("#modalbg").show();
		$("#modalbg").click(function(){ 
			$("#modal").hide(); 
			$("#modalbg").hide();
			$("body").removeClass("modal-open")
		});

	});

	$("#namesearch").on("keyup", function(){
		var s = $(this).val().toLowerCase();
		var tr = $(".main-data tr");
		s_results = 0;
		s_search = s;
		if(s == ""){
			s_results = tr.length;
			updateTitle();
			tr.css("display", "");
			return;
		}
		tr.each(function(){
			if(!$(this).attr("data-name")) return;
			if( 
				$(this).attr("data-name").toLowerCase().indexOf( s ) !== -1 ||
				$(this).attr("data-model").toLowerCase().indexOf( s ) !== -1 ||
				$(this).attr("data-serial").toLowerCase().indexOf( s ) !== -1 ||
				$(this).attr("data-location").toLowerCase().indexOf( s ) !== -1
			){
				$(this).css("display", "");
				s_results++;
				updateTitle();
			}else{
				$(this).css("display", "none");
			}
		});
	});

	$("form[data-status]").submit(function(e){

		$("#status-bar").html("Loading...");

		$.post( $(this).attr("action"), $(this).serialize(), function(data){
			$("#status-bar").html(data);
			$("#status-bar").css("background-color", "#44C666");
			$("#status-bar").animate({ backgroundColor: "#2C4563" }, 3000);
		});

		e.preventDefault();

	});

});