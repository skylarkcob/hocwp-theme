<?php
defined( 'ABSPATH' ) || exit;

trait HT_VR_Tour_Template {
	public function index() {
		require_once( HTE_VR()->folder_path . '/setup.php' );
		?>
        <!DOCTYPE html>
		<?php hocwp_theme_html_tag( 'html', '', get_language_attributes() ); ?>
        <head>
			<?php wp_head(); ?>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <meta name="viewport" id="metaViewport"
                  content="user-scalable=no, initial-scale=1, width=device-width, viewport-fit=cover"
                  data-tdv-general-scale="0.5"/>
            <meta name="apple-mobile-web-app-capable" content="yes"/>
            <meta name="apple-mobile-web-app-status-bar-style" content="default">
        </head>
		<?php
		hocwp_theme_html_tag( 'body' ); // Open body

		if ( HT_Util()->is_vr_theme() ) {
			wp_body_open();
			?>
            <div class="ldcuong-container">
                <div id="pano" style="width:100%;height:100%;">
                    <noscript>
                        <table style="width:100%;height:100%;">
                            <tr style="vertical-align:middle;">
                                <td>
                                    <div style="text-align:center;"><?php echo HT_Message()->noscript(); ?></div>
                                </td>
                            </tr>
                        </table>
                    </noscript>
                </div>
				<?php wp_footer(); ?>
                <script src="tour.js"></script>
                <script>
                    (function ($) {
                        const body = $("body");

                        let isDebug = parseInt(hocwpTheme.is_debug),
                            settings = {
                                xml: "tour.xml",
                                target: "pano",
                                html5: "only",
                                mobilescale: 1.0,
                                passQueryParameters: true,
                                consolelog: true,
                                onready: function (krpano) {
                                    body.on("click", "img.toggle-hotspots", function (e) {
                                        e.preventDefault();

                                        var that = this,
                                            element = $(that);

                                        element.toggleClass("active");

                                        if (element.hasClass("active")) {
                                            krpano.call("hide_all_hot_spots()");
                                        } else {
                                            krpano.call("show_all_hot_spots()");
                                        }
                                    });

                                    body.on("click", ".control-rotate img", function (e) {
                                        e.preventDefault();

                                        var that = this,
                                            element = $(that),
                                            action = element.attr("data-action");

                                        if ("rotate_on" === action) {
                                            krpano.call("stop_auto_tour()");
                                        } else {
                                            krpano.call("start_auto_tour()");
                                        }
                                    });
                                }
                            };

                        if (1 !== isDebug) {
                            settings.consolelog = false;
                        }

                        embedpano(settings);
                    })(jQuery);
                </script>
            </div>
			<?php
		} else {
			echo wpautop( HT_Message()->theme_or_site_incorrect_config() );
		}

		hocwp_theme_html_tag_close( 'body' ); // Close body
		hocwp_theme_html_tag_close( 'html' ); // Close html
	}
}