jQuery(function($){
        $('.mini-cart').mouseover(function(e){
            $(this).children('.topCartContent').fadeIn(200);
            return false;
        }).mouseleave(function(e){
            $(this).children('.topCartContent').fadeOut(200);
            return false;
        });
    }); 
function init(group_id,menu_type,responsivepoint){
	console.log('init_start');
	cwstogglemenu(group_id);
	cwsactivemenu(menu_type);
	
	var responsive_breakpoint = responsivepoint;
	var resizeScreen = responsive_breakpoint.replace("px", "");
	var resizeTimer;
	var groupId = group_id;
	jQuery('ul.smart-expand').find("li.active").children().children("span.arw").removeClass("plush").addClass('minus');
	jQuery(".cwsMenu .Level0 > .hideTitle").each(function() {
					jQuery(this).find(".Level1 > li.Level2").removeClass("Level2").addClass("Level1");
					jQuery(this).find(".Level2 > li.Level3").removeClass("Level3").addClass("Level2");
					jQuery(this).find(".Level3 > li.Level4").removeClass("Level4").addClass("Level3");
					jQuery(this).find(".Level4 > li.Level5").removeClass("Level5").addClass("Level4");
					//alert(this);
				});
				
				var toplinkHeight = jQuery(".mega-menu.horizontal > li").height();
				var toplinkdisplay = "none";
				jQuery(".mega-menu.horizontal > li > ul.Level0").css("top",toplinkHeight);
				
				var spadding = parseInt(jQuery(groupId+" ul.smart-expand a.Level3").css("padding-left"));
				jQuery(groupId+" ul.smart-expand a.Level4").css("padding-left",spadding+10);
				jQuery(groupId+" ul.smart-expand a.Level5").css("padding-left",spadding+20);
				
				var apadding = parseInt(jQuery(groupId+" ul.always-expand a.Level3").css("padding-left"));
				jQuery(groupId+" ul.always-expand a.Level4").css("padding-left",apadding+10);
				jQuery(groupId+" ul.always-expand a.Level5").css("padding-left",apadding+20);
				
				
}
function cwstogglemenu(group_id) {
			
		
			jQuery(group_id+" > .cwsMenu li span.arw").click(function(e){
				console.log(group_id+" > .cwsMenu li span.arw used");
		
		
				e.stopImmediatePropagation();
				if(jQuery(this).hasClass("plush")) {
					if(jQuery(this).parent().next("ul").find("li").hasClass("hideTitle")) {
						jQuery(this).parent().next("ul").find("li").next("ul").slideDown();
					}
					jQuery(this).parent().next("ul").slideDown();
					jQuery(this).parent().next(".cmsbk").slideDown();
					jQuery(this).removeClass("plush");
					jQuery(this).addClass("minus");
				} else {
					jQuery(this).parent().next("ul").slideUp();
					jQuery(this).parent().next(".cmsbk").slideUp();
					jQuery(this).addClass("plush");
					jQuery(this).removeClass("minus");
				}
				return false;
			});
			
		}
		function cwsactivemenu(menu_type) {
			
			var pathname = window.location.pathname; // Returns path only
			var url      = window.location.href; 
			
			if(menu_type=='list-item'){
				jQuery(".cwsMenu li").removeClass("active");
				jQuery("a[href~='"+url+"']").parents().addClass("active");
				jQuery(this).find("li.active").parents('li').addClass('active');
				jQuery(this).find("li.active").parents('li').addClass('active');
				jQuery(".active").parents(this.options.group_id+" ul > li").addClass('active');
			}else{
				jQuery(".cwsMenu li").removeClass("active");
				jQuery("a[href~='"+url+"']").parents().addClass("active");	
			}
		}