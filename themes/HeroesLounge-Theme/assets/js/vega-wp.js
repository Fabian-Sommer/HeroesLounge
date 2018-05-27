
/*jquery-scrollstop*/
!function (factory) { "function" == typeof define && define.amd ? define(["jquery"], factory) : "object" == typeof exports ? module.exports = factory(require("jquery")) : factory(jQuery) }(function ($) { var dispatch = $.event.dispatch || $.event.handle, special = $.event.special, uid1 = "D" + new Date, uid2 = "D" + (+new Date + 1); special.scrollstart = { setup: function (data) { var timer, _data = $.extend({ latency: special.scrollstop.latency }, data), handler = function (evt) { var _self = this, _args = arguments; timer ? clearTimeout(timer) : (evt.type = "scrollstart", dispatch.apply(_self, _args)), timer = setTimeout(function () { timer = null }, _data.latency) }; $(this).bind("scroll", handler).data(uid1, handler) }, teardown: function () { $(this).unbind("scroll", $(this).data(uid1)) } }, special.scrollstop = { latency: 250, setup: function (data) { var timer, _data = $.extend({ latency: special.scrollstop.latency }, data), handler = function (evt) { var _self = this, _args = arguments; timer && clearTimeout(timer), timer = setTimeout(function () { timer = null, evt.type = "scrollstop", dispatch.apply(_self, _args) }, _data.latency) }; $(this).bind("scroll", handler).data(uid2, handler) }, teardown: function () { $(this).unbind("scroll", $(this).data(uid2)) } } });


/* one-page scrolling
------------------------------------------------------------------------*/

jQuery(document).ready(function ($) {

	var header_height = $('.header').height();

	$(window).scroll(function () {
		var scroll = $(window).scrollTop();
		var navbar = $(".navbar-custom");
		if (header_height > 0) {
			if (scroll >= header_height) {
				navbar.addClass("fixed-top");
			} else {
				navbar.removeClass("fixed-top");
			}
		}
	});

	$(window).resize(function () {
		resize_nav_wrapper();
	});
	resize_nav_wrapper();

	$('.menu-header .page-scroll a').click(function () {
		jQuery('.menu-header .page-scroll>a').removeClass('showing');
		jQuery(this).addClass('showing');
		var href = jQuery(this).attr('href').split('#');
		var Header_height = jQuery('.navbar-custom').innerHeight();
		if (href[1]) {
			if (jQuery('#' + href[1]).length > 0) {
				var posEle = jQuery('#' + href[1]).offset().top - Header_height;
				jQuery('html,body').animate({ scrollTop: posEle }, 600);
				return false;
			}
		}
	});
	$(window).load(function () {
		var hash = window.location.hash;
		var Header_height = jQuery('.navbar-custom').innerHeight();
		if (hash) {
			if (jQuery(hash).length > 0) {
				var posEle = jQuery(hash).offset().top - Header_height;
				jQuery('html,body').animate({ scrollTop: posEle }, 600);
				return false;
			}
		}
	});
	$('.navbar-custom').append('<div id="xxxx" style="position:absolute;right:0;top:0"></div>');
	$(window).scroll(function () {
		$('.section').each(function () {
			//var visible = jQuery(this).visible('false'); 
			var visible = isScrolledIntoView(jQuery(this));
			if (visible) {
				var id = jQuery(this).attr('id');
				if (jQuery(".menu-header .page-scroll > a[href*='" + id + "']").length > 0) {
					jQuery('.menu-header .page-scroll > a').removeClass('showing');
					jQuery(".menu-header .page-scroll > a[href*='" + id + "']").addClass('showing');
				}
			}
		});
	});
	$(".header-toggle").click(function () {
		$(this).toggleClass('open');
		$('.header').toggleClass('open');
		return false;
	});
});

function isScrolledIntoView(elem) {
	var $elem = jQuery(elem);
	var $window = jQuery(window);
	var $navbar = jQuery(".navbar-custom");

	var docViewTop = $window.scrollTop();
	var docViewBottom = docViewTop + $window.height();

	var elemTop = $elem.offset().top - $navbar.innerHeight() - 20;
	var elemBottom = elemTop + $elem.height();
	return ((docViewTop >= elemTop));
}

function resize_nav_wrapper() {
	var header_height = jQuery('.header').height();
	var navbar_custom_height = jQuery('.navbar-custom').height();
    /*if(header_height > 0 && jQuery( window ).width() >= 768) {
        //console.log('navbar_custom_height=' + navbar_custom_height);
        jQuery('.nav-wrapper').height(navbar_custom_height);
    } else jQuery('.body_padding').css("padding-top", navbar_custom_height); */
}

/* back to top, window resizing
------------------------------------------------------------------------*/

jQuery(document).ready(function ($) {

	//back to top
	$(window).scroll(function () {
		if ($(window).scrollTop() > 100) {
			if (!$('#back_to_top').hasClass('show'))
				$('#back_to_top').addClass('show');
			if (!$('#back_to_top').hasClass('scroll'))
				$('#back_to_top').addClass('scroll');
		}
		else
			$('#back_to_top').removeClass('show');
	});
	$(window).on('scrollstop', function () {
		$('#back_to_top').removeClass('scroll');
	});
	if ($(window).scrollTop() > 100) $('#back_to_top').addClass('show');
	else $('#back_to_top').removeClass('show');
	$('#back_to_top').click(function () {
		$('html,body').stop().animate({ scrollTop: 0 }, 1000);
		return false;
	});


	//window resizing, full screen banners
	$(window).resize(function () {
		EventOnResize();
	});
	EventOnResize();


});

/* resizing elements
------------------------------------------------------------------------*/

function EventOnResize() {
	var $wHieght = jQuery(window).height();
	var $nhHieght = jQuery('.navbar-custom').height();
	var $nnH = $wHieght - $nhHieght;
	jQuery('.image-banner.full-screen').css('height', $nnH + 'px');

	jQuery('.row').each(function () {
		if (jQuery(this).find('.content-icon').length > 0) {
			jQuery('.content-icon .title', this).css('min-height', 'inherit')
			jQuery('.content-icon .body', this).css('min-height', 'inherit')
			var titleH = 0;
			var bodyH = 0;
			jQuery('>div', this).each(function () {
				var titleHn = jQuery('.content-icon .title', this).height();
				var bodyHn = jQuery('.content-icon .body', this).height();
				if (titleHn > titleH) titleH = titleHn;
				if (bodyHn > bodyH) bodyH = bodyHn;
			});
			jQuery('.content-icon .title', this).css('min-height', titleH)
			jQuery('.content-icon .body', this).css('min-height', bodyH)
		}

		if (jQuery(this).find('.recent-entry').length > 0) {
			jQuery('.recent-entry .recent-entry-title', this).css('min-height', 'inherit')
			jQuery('.recent-entry .recent-entry-content', this).css('min-height', 'inherit')
			var titleH = 0;
			var bodyH = 0;
			jQuery('>div', this).each(function () {
				var titleHn = jQuery('.recent-entry .recent-entry-title', this).height();
				var bodyHn = jQuery('.recent-entry .recent-entry-content', this).height();
				if (titleHn > titleH) titleH = titleHn;
				if (bodyHn > bodyH) bodyH = bodyHn;
			});
			jQuery('.recent-entry .recent-entry-title', this).css('min-height', titleH);
			jQuery('.recent-entry .recent-entry-content', this).css('min-height', bodyH + 15); /* 15= recent-entry-content padding-bottom */
		}
	});
}