/*
// KJR 2014-08-22
// Global JS included on every page.  Add all your common script inits here.
*/

//$(document).ready(function() {
$(function() { // Shorthand for $( document ).ready()
	// executes when HTML-Document is loaded and DOM is ready

	//init SVG backward compatibility script
	svgeezy.init(false, 'png');

	(function($) {
	    $.fn.drags = function(opt) {

	        opt = $.extend({handle:"",cursor:"move"}, opt);

	        if(opt.handle === "") {
	            var $el = this;
	        } else {
	            var $el = this.find(opt.handle);
	        }

	        return $el.css('cursor', opt.cursor).on("mousedown", function(e) {
	            if(opt.handle === "") {
	                var $drag = $(this).addClass('draggable');
	            } else {
	                var $drag = $(this).addClass('active-handle').parent().addClass('draggable');
	            }
	            var z_idx = $drag.css('z-index'),
	                //drg_h = $drag.outerHeight(),
	                drg_w = $drag.outerWidth(),
	                //pos_y = $drag.offset().top + drg_h - e.pageY,
	                pos_x = $drag.offset().left + drg_w - e.pageX;
	            $drag.css('z-index', 1000).parents().on("mousemove", function(e) {
	                $('.draggable').offset({
	                    //top:e.pageY + pos_y - drg_h,
	                    left: e.pageX + pos_x - drg_w
	                }).on("mouseup", function() {
	                    $(this).removeClass('draggable').css('z-index', z_idx);
	                });
	            });
	            console.log('pos_x:'+pos_x);
	            console.log($(this).css('left'));
	            if ($(this).css('left') > 0) {
	            	$(this).css('left', 0);
	            }
	            e.preventDefault(); // disable selection
	        }).on("mouseup", function() {
	            if(opt.handle === "") {
	                $(this).removeClass('draggable');
	            } else {
	                $(this).removeClass('active-handle').parent().removeClass('draggable');
	            }
	        });

	    }
	})(jQuery);

	$('.table1').drags();
});

$(function(){
    var current = location.pathname;
    $('#nav li a').each(function(){
        var $this = $(this);
        // if the current path is like this link, make it active
        if($this.attr('href').indexOf(current) !== -1){
            $this.addClass('active');
        }
    })
})

$(window).load(function() {
	// executes when complete page is fully loaded, including all frames, objects and images
});

