$(document).ready(function() {
    // BTN: add-height
	$('#btn-add-height').click(function() {
        if($('#page-height-control').hasClass('add-height')){
			$('#page-height-control').removeClass('add-height');
		}else{
			$('#page-height-control').addClass('add-height');
		}
    });
	
	// Switch: switch-header-fixed
	$("#switch-header-fixed").change(function() {
       if(this.checked) {
          $('body').addClass('layout-navbar-fixed');
       }else{
		  $('body').removeClass('layout-navbar-fixed');  
	   }
    });
	
	// Enable popovers everywhere
	$(function () {
     $('[data-toggle="popover"]').popover()
    });
	
	// Hide popovers on clicks outside popover
	$('html').on('click', function(e) {
      if (typeof $(e.target).data('original-title') == 'undefined' &&
        !$(e.target).parents().is('.popover.in')) {
			if($(e.target).parents('.popover').length == 0){
                    $('[data-original-title]').popover('hide');
			}
        }
    });
	
	// Handle Header Text Color
	$("#switch-header-txt-color").change(function() {
       if(this.checked) {
		   $('.main-header').removeClass('navbar-text-dark'); 
          $('.main-header').addClass('navbar-text-light');
       }else{
		  $('.main-header').removeClass('navbar-text-light'); 
          $('.main-header').addClass('navbar-text-dark');		  
	   }
    });
	// Handle Header background Color
	$('.headercolor').click(function() {
		var txtColor= $('#switch-header-txt-color').is(":checked") == true ? 'navbar-text-light' : 'navbar-text-dark';
		var classcolor= (this.className.split(' ')[1]);
       $('.main-header').attr('class', 'main-header navbar navbar-expand' +' ' + txtColor + ' ' + classcolor);
    });
	
	//
	
	
	// switch-sidebar-type
	$("#switch-sidebar-type").change(function() {
       if(!this.checked) {
	      
	    $(".collapse-sm .sidebar .nav-link").unbind('mouseover');
	 	$(".collapse-sm .sidebar .nav-link p").removeAttr("style");
		   
	    $('body').removeClass('collapse-lg');
        $('body').addClass('collapse-sm sidebar-collapse');	
		$("#switch-sidebar-collapse-type").prop('checked', false);
		$("#switch-sidebar-collapse-type").prop('disabled', true);
		
		$("#switch-sidebar-flat-type").prop('disabled', false);
		
		 resetSideBarColor();  
		 TabtooltipInit();
	   } else{
		   // Tnagele SideBar
 
		   $("#switch-sidebar-collapse-type").prop('disabled', false);
		   
		   $("#switch-sidebar-flat-type").prop('checked', false);
		   $("#switch-sidebar-flat-type").prop('disabled', true);
		   
		   $('.nav-sidebar').addClass('nav-flat');
		    resetSideBarColor();  
			TabtooltipInit();
	   }
		   
	});
	
	// switch-sidebar-type
	$("#switch-sidebar-flat-type").change(function() {
       if(!this.checked) {
	          $('.nav-sidebar').addClass('nav-flat');
	   } else{
		     $('.nav-sidebar').removeClass('nav-flat');
	   }
		   
	});
	
	
	// Handle Sidebar Collapse Type (Switch)
	$("#switch-sidebar-collapse-type").change(function() {
       if(this.checked) {
		   $(".collapse-sm .sidebar .nav-link").unbind('mouseover');
		   $(".collapse-sm .sidebar .nav-link p").removeAttr("style");
		   
		  $('body').removeClass('collapse-sm');
          $('body').addClass('collapse-lg sidebar-collapse');
		  
       }else{
		   $('body').removeClass('collapse-lg');
          $('body').addClass('collapse-sm sidebar-collapse');
		   TabtooltipInit();
	   }
    });
	//
	// Show tab title tooltip on hover (sidebar-collapse)
	function TabtooltipInit(){
    $(" .collapse-sm  .sidebar  .nav-link").unbind("mouseover").bind("mouseover",function(e){	 
		var Selector = $('.sidebar .os-viewport').length  == 0 ?  '.sidebar' : '.sidebar .os-viewport' ; // (.os-viewport) if overlayScrollbars is enabled, otherwise (.sidebar)
		if ($(this).children('p')) {
            $(this).children('p').css("top", (($(this).position().top + 160) - $(Selector).scrollTop()) + 'px'); // without overlayScrollbars
        }
    });
	}
	
	// Handle Sidebar Background Color
	$("#switch-sidebar-bg-color").change(function() {
	         resetSideBarColor();
    });
	
	// Check SideBar Color
	function resetSideBarColor(){
		var classname= ($('.main-sidebar').attr('class')).split(' ');
	var colorclass='';
	var colorname='';
	var darklight='';
	for(var i=0; i < classname.length; i++){
		if(classname[i].substring(0,13) == 'sidebar-dark-'){
			colorclass = classname[i];
			if(classname[i].split('-')[2] == 'tnagele')
			{
				colorname = classname[i].split('-')[3];
			}else{
				colorname = classname[i].split('-')[2];
			}
			
			break;
		}else if(classname[i].substring(0,14) == 'sidebar-light-'){
		    colorclass = classname[i];
			if(classname[i].split('-')[2] == 'tnagele')
			{
				colorname = classname[i].split('-')[3];
			}else{
				colorname = classname[i].split('-')[2];
			}
			break;
		}
			
	}
	   var istnagele = $('#switch-sidebar-type').is(":checked") == true ? 'tnagele-' : '';
	   var darklight =$('#switch-sidebar-bg-color').is(":checked") == true ? 'dark' : 'light';
	   var  nodarklight = darklight == 'dark' ? 'light'  :  'dark' ;
       
		   $('.main-sidebar').removeClass(colorclass); 
          $('.main-sidebar').addClass('sidebar-'+ darklight+'-'+ istnagele + colorname);
		  
		   $('.sidebar').removeClass('os-theme-dark').removeClass('os-theme-light').addClass('os-theme-'+nodarklight);//
       
	};
	// Handle SideBar Front Color
	$('.sidebarcolor').click(function() {
		var classname= ($('.main-sidebar').attr('class')).split(' ');
	var colorclass='';
	for(var i=0; i < classname.length; i++){
		if(classname[i].substring(0,13) == 'sidebar-dark-'){
			colorclass = classname[i];	
			break;
		}else if(classname[i].substring(0,14) == 'sidebar-light-'){
		    colorclass = classname[i];
			break;
		}
			
	}
	    $('.main-sidebar').removeClass(colorclass); 
		var darkLight= $('#switch-sidebar-bg-color').is(":checked") == true ? 'dark' : 'light';
		var classcolor= (this.className.split(' ')[1]).split('-')[1];
		var istnagele = $('#switch-sidebar-type').is(":checked") == true ? 'tnagele-' : '';
        $('.main-sidebar').addClass( 'sidebar-' +darkLight+'-'+ istnagele + classcolor);
    });
	
	
	// Switch: switch-header-fixed
	$("#switch-footer-fixed").change(function() {
       if(!this.checked) {
          $('body').addClass('layout-footer-fixed');
       }else{
		  $('body').removeClass('layout-footer-fixed');  
	   }
    });
	
	
	// Handle Footer Text Color
	$("#switch-footer-txt-color").change(function() {
       if(this.checked) {
		   $('.main-footer').removeClass('navbar-text-dark'); 
          $('.main-footer').addClass('navbar-text-light');
       }else{
		  $('.main-footer').removeClass('navbar-text-light'); 
          $('.main-footer').addClass('navbar-text-dark');		  
	   }
    });
	// Handle Footer background Color
	$('.footercolor').click(function() {
		var txtColor= $('#switch-footer-txt-color').is(":checked") == true ? 'navbar-text-light' : 'navbar-text-dark';
		var classcolor= (this.className.split(' ')[1]);
       $('.main-footer').attr('class', 'main-footer ' +' ' + txtColor + ' ' + classcolor);
    });
	
	// Apply Font
	$('#btn-apply-font').click(function() {
		$('#fontstyle').remove();
		var font_url= $('#font-url').val() ;
		var font_name= $('#font-family').val();
		var style_css= "@import url('"  + font_url + "'); * {font-family: "+ font_name +";}";
		 $('<style id="fontstyle" type="text/css"></style>').text(style_css).appendTo('head');
	});
	


});