(function($) {
	$(document).ready(function () {
		//验证码刷新
		$('img.fresh_authcode').click(function () {
			var src = $(this).attr('src');
			var base_url = src.substring(0, src.indexOf('?'));
			$(this).attr('src', base_url + "?" +new Date().getTime());
		});	
		// Register form
		$('#user_register_form').ajaxForm({
			success: function (responseText, statusText, xhr, $form) {
				var data = $.parseJSON(responseText);
				if (data.success == 1) {
					window.location = "/user";
				}
				else {
					alert(data.message);
				}
			}
		});

		// Login form
		$('#user_login_form').ajaxForm({
			success: function(responseText) {
				var data = $.parseJSON(responseText);
				if (data.success == 1) {
					alert("登录成功");
					window.location.reload();
				}
				else {
					alert(data.message);
				}
			}
		});

		//邮件分享游戏结果
		$("#user_game_share_result button[name='share']").click(function () {
			$('#user_game_share_result').ajaxSubmit(function (data) {
				data = $.parseJSON(data);
				alert(data.message);
			});
		});

		//社区分享游戏结果
		$("#social_brand").click(function() {
			// 游戏完成后， 更新游戏玩家状态
			var gid = $('input[name="gid"]').val();
			$.ajax({
				url: '/game/game_social_share',
				data: {
					social_brand: 'qq',
					gid: gid
				},
				type: 'POST',
				success: function (data) {
					data = $.parseJSON(data);
					if (data.success == 1) {
						alert("分享成功");
					}
				}
			});
		});

		// start game.
		init_minigame("minigame_section_container");
	});

	if (!Array.prototype.indexOf)
	{
	  Array.prototype.indexOf = function(elt /*, from*/)
	  {
	    var len = this.length;

	    var from = Number(arguments[1]) || 0;
	    from = (from < 0)
	         ? Math.ceil(from)
	         : Math.floor(from);
	    if (from < 0)
	      from += len;

	    for (; from < len; from++)
	    {
	      if (from in this &&
	          this[from] === elt)
	        return from;
	    }
	    return -1;
	  };
	}

	function init_minigame(container_id) {
		var container = $('#' + container_id);
		var random_iamges = [];
		for (var i = 1; i < 11; i++) {
			while (true) {
				var random_iamge_index = parseInt(Math.random() * 10 * 2);
				var index_of = random_iamges.indexOf(random_iamge_index);
				if (index_of == -1) {
					random_iamges.push(random_iamge_index);
					break;
				}
			}
		}
		for (i = 0; i < 20; i++) {
			var has_img = random_iamges.indexOf(i) == -1 ? false : true;
			container.append(render_sigle_element(i, has_img));
		}

		// bind event
		bind_sigle_element_event(container, {
			finished: function () {
				$('#user_game_share_result').show();
				// 游戏完成后， 更新游戏玩家状态
				var uid = $('input[name="uid"]').val();
				var gid = $('input[name="gid"]').val();
				$.ajax({
					url: '/user/user_game_is_finished',
					data: {
						uid: uid,
						gid: gid
					},
					method: "POST",
					success: function (data) {
						data = $.parseJSON(data);
						console.log(data);
					}
				});

				alert("游戏已经完成 请分享");
			},
			clicked: function () {
				// 点击图片后 传送服务器 增加积分
				$('#user_submit_game_form').ajaxSubmit(
					function(responseText) {
						console.log(responseText);
					}
				);
			},
			unclicked: function() {
				//TODO:点中非图片区域
			}
		});
	}

	function render_sigle_element(index, has_img) {
		var base_url = $('base').attr('href');
		if (has_img) {
			var image = "<img src='/application/public/icons/icons-390.jpg' class='has-img img' width='50px' height='50px' />";
		}
		else {
			var image = "<img src='' class='img no-img' width='50px' height='50px' />";
		}
		var sigle_element = "<div class='game-sigle-element'>";
		sigle_element += image;
		sigle_element += '</div>';
		return sigle_element;
	}

	function bind_sigle_element_event(container, options) {
		options || (options = {});
		$('img.has-img', container).click(function () {
			if (!$(this).hasClass('has-img-clicked')) {
				$(this).addClass('has-img-clicked');
				if (options.clicked) options.clicked($(this));
			}

			if ($('img.has-img-clicked', container).size() == parseInt($("#user_submit_game_form input[name='max_game_element']").val())) {
				if (options.finished) options.finished($(this));
			}
		});
		$('img.no-img', container).click(function () {
			$(this).addClass('no-img-clicked');
			if (options.unclicked) options.unclicked($(this));
		});
	}


})(jQuery);