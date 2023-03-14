$(document).ready(function() {

    // initiate overlayScrollbars
    $('.sidebar').overlayScrollbars({
        className: 'os-theme-light deviant-scrollbars',
        sizeAutoCapable: true,
        scrollbars: {
            autoHide: 'scroll', //   "never", "scroll", "leave", "move"  
            clickScrolling: true
        },
		overflowBehavior : {
		x : "hidden",
		y : "scroll"
	    }
   });
   
  
    // Show tab title tooltip on hover (sidebar-collapse)
	$(" .collapse-sm  .sidebar  .nav-link").unbind("mouseover").bind("mouseover",function(e){	 
		var Selector = $('.sidebar .os-viewport').length  == 0 ?  '.sidebar' : '.sidebar .os-viewport' ; // (.os-viewport) if overlayScrollbars is enabled, otherwise (.sidebar)
	  if ($(this).children('p')) {
            $(this).children('p').css("top", (($(this).position().top + 155) - $(Selector).scrollTop()) + 'px'); // without overlayScrollbars
        }
    });

    // Toggles Treeview: active/ inactive
    $('.sidebar .nav-link').click(function() {
        $('a').removeClass('active');
        $(this).addClass('active');
        // if parent has-treeview will be active
        $(this).parents('.has-treeview').find('.nav-link').first().addClass("active");
    });

    // 	Toggles Treeview: slideUp/slideDown
    $(".has-treeview > a").click(function() {
        //  $(".nav-treeview").slideUp(100);

        if ($(this).parent().hasClass("menu-open")) {
            $(this).siblings('.nav-treeview').first().slideUp(100);
            $(this).parent().removeClass("menu-open");
        } else {

            $(this).siblings(".nav-treeview").slideDown(100);
            $(this).parent().addClass("menu-open");
        }
    });

    function toggle() {
        if ($(window).width() <= 992) {
            if ($('body').hasClass('sidebar-open')) {
                $('body').removeClass('sidebar-open');
                $('body').addClass('sidebar-collapse');
            } else {
                 
               
                $('body').removeClass('sidebar-collapse');
                $('body').addClass('sidebar-open');
            }

        } else {
            if ($('body').hasClass('sidebar-collapse')) {
                $('body').removeClass('sidebar-collapse');
				localStorage.setItem('sidebarstatus', 'notcollapsed');
            } else {
                $('body').addClass('sidebar-collapse');
				localStorage.setItem('sidebarstatus', 'collapsed');
            }
        }
    };
	
	 $('#sidebar-overlay').on('click', function() {
         $('body').removeClass('sidebar-open');
        $('body').addClass('sidebar-collapse');
     });
				
	// Remember sidebar-collapse Status on Start
	if(localStorage.getItem('sidebarstatus') == 'collapsed'){
		$('body').addClass('sidebar-collapse');
	}
	
    // Toggles PushMenu 
    $('#pushmenu').click(function() {
        toggle();

    });

    $('#pushmenu-sidebar').click(function() {
         toggle();
    });

    // 
    if ($(window).width() <= 992) {
        $('body').removeClass('sidebar-open');
        $('body').addClass('sidebar-collapse');
    }

});