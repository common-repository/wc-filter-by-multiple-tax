(function($) {

	'use strict';
	// Taxonomy Multi Level Filter
	var current_page = Main_Widget_Filter['current_page'];

    function get_obj_feature(){

        var feature_ = new Map(),
			container = $('.product-taxonomy');
        if (container.data['keys'] === 'feature'){

            container.find('> li').each(function() {

                var id = $(this).attr('class').match(/\d+/)[0];
                if( $(this).find('.children').length ){
                    var ch_ = [];
                    $(this).find('.children > li').each(function() {
                        var id_ = $(this).attr('class').match(/\d+/)[0];
                        ch_.push(id_);
                    });
                    feature_.set(id,ch_);
                } else {
                    feature_.set(id,id);
                }

            });

        }

        return feature_;

    }

    if( $('.taxonomy-filter').length ){

    	if( ! $('.taxonomy-filter').hasClass('product_cat') ) {
			$('.taxonomy-filter ul.children').parent().addClass('no-filter');
    	}

		$('.taxonomy-filter').on('click', 'a', function(event) {

            var par = $(this).parent(),
            	nex = par.find(' > ul').attr('class');

			event.preventDefault();
			var current_url = window.location.href,
				id          = $(this).parent().attr('class').match(/\d+/)[0],
				key         = $(this).parents('.taxonomy-filter').data('keys'),
				data        = current_url.split('/?')[1],
				href        = current_url.split('/?')[0];

			if( current_page !== null )
				href = current_page;

			var obj_feature = get_obj_feature();

			// Remove Term of Taxonomy in Filter
			if( $(this).parent().hasClass('active') ){

				if( data.indexOf(',') === -1 ){
					data = data.replace('_'+key+'='+id, '');
				} else {
					if( key === 'feature' ){
						data = data.replace(id + ',', '').replace(',' + id, '');
					} else {
						data = data.replace('_'+key+'='+id, '');
					}
				}
				data = data.replace('?&','?');


			} else {

				if( data !== undefined ){

					if( key === 'feature' && data.indexOf(key) !== -1 ){

						var data_ = data.match(/\b(\w+)=(\d+)\b/g);
						if( data.indexOf('&') !== -1 ){
							data_ = data.split('&');
						} else {
							data_ = [data];
						}

						var method = '';

						$.each(data_, function(k, v) {

							if( v.indexOf(key) === -1 ){

								if( method === '' ){
									method += v;
								}
								else{
									method += '&' + v;
								}

							} else {

								if( v.indexOf(',') === -1 ){

									var id_ = v.split('=')[1];

									if( obj_feature.has(id_) && obj_feature.has(id) ){

										if( method === '' ){
											method =  '_' + key + '=' + id_ + ',' + id;
										} else {
											method +=  '&_' + key + '=' + id_ + ',' + id;
										}

									} else if( obj_feature.has(id) && !obj_feature.has(id_) ) {

										var parent = obj_feature.get(id);

										if( $.inArray(id_, parent) === -1 ){

											if( method === '' ){
												method = '_' + key + '=' + id_ + ',' + id;
											} else {
												method += '&_' + key + '=' + id_ + ',' + id;
											}

										} else {

											if( method === '' ){
												method = '_' + key + '=' + id;
											} else {
												method += '&_' + key + '=' + id;
											}

										}

									} else if ( obj_feature.has(id_) && !obj_feature.has(id) ){

										var parent_ = obj_feature.get(id_);

										if( $.inArray(id, parent_) === -1 ){

											if( method === '' ){
												method = '_' + key + '=' + id_ + ',' + id;
											} else {
												method += '&_' + key + '=' + id_ + ',' + id;
											}

										} else {

											if( method === '' ){
												method = '_' + key + '=' + id;
											} else {
												method += '&_' + key + '=' + id;
											}

										}

									} else {

										var pr  = $('.cat-item-'+id).parent('.children').parent().attr('class'),
											pr_ = $('.cat-item-'+id_).parent('.children').parent().attr('class');

										if( pr === pr_ ){

											if( method === '' ){
												method = '_' + key + '=' + id;
											} else {
												method += '&_' + key + '=' + id;
											}

										} else {

											if( method === '' ){
												method = '_' + key + '=' + id_ + ',' + id;
											} else {
												method += '&_' + key + '=' + id_ + ',' + id;
											}

										}

									}

								} else {

									var id__ = v.split('=')[1].split(',');

									if( obj_feature.has(id) ){

										var parent__ = obj_feature.get(id);

										$.each(id__,function(k_, v_) {

											if( $.inArray(v_, parent__) !== -1 ){

												if( method === '' ){
													method = v.replace( v_,id );
												} else {
													method += '&'+v.replace( v_,id );
												}

											}

										});

									} else {
										var id_repleace = '',
											is_parent   = [];
										$.each(id__,function(k_, v_) {

											var parent = obj_feature.get(v_),
												li_pr  = $('.cat-item-'+id).parent('.children').parent().attr('class'),
												li_pr_ = $('.cat-item-'+v_).parent('.children').parent().attr('class');

											if( parent !== undefined ){

												is_parent.push('yes');
												if( $.inArray(id,parent) !== -1 ){
													id_repleace = v_;
												}

											} else {
												is_parent.push('no');
												if( li_pr === li_pr_ ){
													is_parent.push('yes');
													id_repleace = v_;
												}
											}

										});

										if( id_repleace !== '' && $.inArray('yes', is_parent) !== -1 ){

											if( method === '' ){
												method = v.replace(id_repleace,id);
											} else {
												method += '&'+v.replace(id_repleace,id);
											}

										} else {
											if( method === '' ){
												method = v + ',' + id;
											} else {
												method += '&'+ v + ',' + id;
											}
										}

									}

								}

							}

						});
						
						data = method;

					} else {

						if( key === 'product_cat' ){
							data = '_' + key + '=' + id;
						} else {

							data = data.replace(/(&?)_feature=[0-9,?]+/,'');
							data = data.replace(/(&?)_product_tag=[0-9,?]+/,'');
							if( data.indexOf('&') === -1 ){

								var key_1 = data.split('=')[0].replace('_', '');
								if( key === key_1 ){
									data = '_'+key_1+'='+id;
								} else {
									data = data + '&_' + key + '=' + id;
								}

							} else {

								var va = data.split('&');
								var k_check = [],
									v_in    = '';
								$.each(va, function(k, v) {
									k_check.push(v.split('=')[0].replace('_',''));
								});

								if( $.inArray(key, k_check) !== -1 ){

									data = '';
									$.each(k_check, function(k, v) {
										if( key === v ){
											v_in = k;
										}
									});

									$.each(va, function(k, v) {
										if( v_in === k){
											if( data !== ''){
												data += '&_' + key + '=' + id;
											} else {
												data += '_'+key + '=' + id;
											}
										} else {
											if( data !== '' ){
												data += '&' + v;
											} else {
												data += v;
											}
										}
									});

								} else {

									data = data + '&_' + key + '=' + id;

								}
							}

						}

					}

				} else {

					data = '_'+ key + '=' + id;

				}

			}

			// Check Taxonomy Multi Level use style Dropdown
			if( $(this).parents('.product-taxonomy').hasClass('dropdown') && $(this).parent().find('.children').length && !$(this).parent().hasClass('open') )
				return;

			if( data !== '' ){
				if( current_page !== null ){
					if( href.indexOf('?') === -1 ){
						window.location.href = href + '?' + data;
					} else {
						window.location.href = href + '&' + data;
					}
				} else {
					window.location.href = href + '/?' + data;
				}
			} else {
				window.location.href = href;
			}
		});

		// Check Term is active
		var current_url = window.location.href,
			data        = current_url.split('/?')[1],
			url 		= current_url.split('/?')[0];

		// Add class active to term use to filter
		if( data !== undefined ){

			var box = '<div class="widget widget-box-selection"><span class="widget-title shop-sidebar">' + Main_Widget_Filter['label_box_selected'] + '</span><a href="'+url+'" class="remove-filter">'+Main_Widget_Filter['label_remove']+'</a>';
			var va = data.match(/\b(\d+)\b/g);
			var box = box + '<ul>';
			$.each(va, function(k, v) {
				var term =  $( '.taxonomy-filter .cat-item-'+v ),
					tax_slug = term.parents('ul.taxonomy-filter').data('keys');
				term.addClass('active');
                term.find('> a').append('<i class="fa fa-times-circle"></i>');
				if( $('.product-taxonomy').hasClass('dropdown') ){
					$( '.taxonomy-filter .active' ).parents('.children').addClass('open');
					$( '.taxonomy-filter .active' ).parents('.children').parent().addClass('open');
				}
				if( term.find(' > a').html() != undefined ){
					box = box + '<li class="cat-item cat-item-'+v+'" data-selected="_'+tax_slug+'='+v+'"><a href="#">' + term.find(' > a').html() + '</a></li>';
				}

			});
			box = box + '</ul></div>';

			$('.widget').parent().find('.widget-taxonomy-menu').first().before(box);

			$('.widget-box-selection ul').on('click', 'li', function(event) {
				event.preventDefault();
				var data_remove      = $(this).data('selected');
				var myRegEx          =  new RegExp('(&?)' + data_remove + '(&?)');
				var remove_url       = current_url.replace(myRegEx,'');
				remove_url           = remove_url.split('/?')[1] ? remove_url : remove_url.split('/?')[0];
				window.location.href = remove_url;
			});

		}

	}

	// Taxonomy Dropdown/List
	if( $('.widget .dropdown').length ){

		if($('.widget .dropdown').hasClass('cat_hide')){
			$('.widget .dropdown.cat_hide').prev('.widget-title').addClass('has_current_cat');
			$('.widget .has_current_cat').click(function() {
				$(this).toggleClass('active');
				$(this).next().slideToggle();
			});
		}

		$('.widget .dropdown').on('click', 'a', function(event) {
			if( $(this).parent().find('.children').length ){
				if( $(this).parent().hasClass('open') ){
					return true;
				}
				event.preventDefault();
				if( $('.widget .dropdown .children').parent().hasClass('open') && !$(this).parents('.children').parent('li').hasClass('open') ){
					$('.widget .dropdown .children').slideUp();
					$('.widget .dropdown .children').parent().removeClass('open');
				}
				$(this).parent().find(' > .children').slideToggle();
				$(this).parent().toggleClass('open');
			}
		});

		if( $('.current-cat-ancestor').length )
			$('.current-cat-ancestor').addClass('open');

		if( $('.widget .dropdown .current-cat').length )
			$('.widget .dropdown .current-cat').addClass('open');

	}

	// Search term in taxonomy
	if( $('.widget .wc-filter-search').length ){
		$('.widget .wc-filter-search').on('keyup', 'input', function(event) {
			event.preventDefault();
			var key = $(this).val().toLowerCase(),
				tax = $(this).parent('.wc-filter-search').next('.product-taxonomy');
			tax.find('a').each(function(index, el) {
				if( el.textContent.toLowerCase().match(key) ){
					el.parentNode.style.display = "block";
				} else {
					el.parentNode.style.display = "none";
				}
			});
		});			
	}

})(jQuery);
