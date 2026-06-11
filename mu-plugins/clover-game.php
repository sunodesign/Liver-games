<?php
/**
 * Plugin Name: Clover Game (ハイ&ロー)
 * Description: ハイ&ロー（High & Low）ゲームをショートコードで埋め込みます。ACFの項目にショートコードを記述して呼び出すことができます。
 * Version:     1.0.0
 * Author:      suno design
 *
 * 使い方:
 *   1) /wp-content/uploads/highlow.html にゲーム本体（HTML）を配置します。
 *   2) 投稿本文・ウィジェット・ACFの項目などに次のショートコードを記述します。
 *
 *        [highlow]
 *
 *      高さや幅を指定したい場合:
 *
 *        [highlow height="640" width="100%"]
 *
 *      別のHTMLファイルを読み込みたい場合:
 *
 *        [highlow src="/wp-content/uploads/my-game.html"]
 *
 *   3) ACFの「テキスト」「テキストエリア」項目に記述したショートコードも
 *      自動的に展開されます（WYSIWYG項目は標準で展開されます）。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // 直接アクセス禁止
}

if ( ! class_exists( 'Clover_Game_HighLow' ) ) :

	class Clover_Game_HighLow {

		/** ショートコードのタグ名 */
		const TAG     = 'highlow';

		/** 旧/別名タグ */
		const TAG_ALT = 'clover_game';

		/** ゲームHTMLの既定パス（uploads ディレクトリ基準のファイル名） */
		const DEFAULT_FILE = 'highlow.html';

		public function __construct() {
			add_shortcode( self::TAG, array( $this, 'render' ) );
			add_shortcode( self::TAG_ALT, array( $this, 'render' ) );

			// ACF のテキスト/テキストエリア項目内のショートコードを展開する
			add_filter( 'acf/format_value/type=text', array( $this, 'do_acf_shortcode' ), 20, 3 );
			add_filter( 'acf/format_value/type=textarea', array( $this, 'do_acf_shortcode' ), 20, 3 );
		}

		/**
		 * 既定のゲームHTMLのURLを返す。
		 *
		 * @return string
		 */
		protected function default_src() {
			$uploads = wp_get_upload_dir();
			$base    = isset( $uploads['baseurl'] ) ? $uploads['baseurl'] : ( content_url() . '/uploads' );

			return trailingslashit( $base ) . self::DEFAULT_FILE;
		}

		/**
		 * ショートコードの描画。
		 *
		 * @param array  $atts    属性。
		 * @param string $content 内包コンテンツ（未使用）。
		 * @param string $tag     タグ名。
		 * @return string
		 */
		public function render( $atts, $content = '', $tag = '' ) {
			$atts = shortcode_atts(
				array(
					'src'    => '',
					'width'  => '100%',
					'height' => '620',
					'title'  => 'ハイ&ロー ゲーム',
				),
				$atts,
				$tag
			);

			$src = $atts['src'] !== '' ? $atts['src'] : $this->default_src();

			// 数値のみの場合は px を付与
			$width  = $this->dimension( $atts['width'] );
			$height = $this->dimension( $atts['height'] );

			$html  = '<div class="clover-game-wrap" style="max-width:480px;margin:0 auto;">';
			$html .= sprintf(
				'<iframe class="clover-game-frame" src="%1$s" title="%2$s" width="%3$s" height="%4$s" loading="lazy" frameborder="0" scrolling="no" style="width:%3$s;height:%4$s;border:0;border-radius:20px;display:block;max-width:100%%;" allowfullscreen></iframe>',
				esc_url( $src ),
				esc_attr( $atts['title'] ),
				esc_attr( $width ),
				esc_attr( $height )
			);
			$html .= '</div>';

			return $html;
		}

		/**
		 * 幅・高さの値を正規化する。数値のみなら px を付ける。
		 *
		 * @param string $value
		 * @return string
		 */
		protected function dimension( $value ) {
			$value = trim( (string) $value );
			if ( $value === '' ) {
				return 'auto';
			}
			if ( is_numeric( $value ) ) {
				return $value . 'px';
			}
			return $value;
		}

		/**
		 * ACF のテキスト系項目の値に含まれるショートコードを展開する。
		 *
		 * @param mixed $value
		 * @param mixed $post_id
		 * @param array $field
		 * @return mixed
		 */
		public function do_acf_shortcode( $value, $post_id = 0, $field = array() ) {
			if ( is_string( $value ) && strpos( $value, '[' ) !== false ) {
				return do_shortcode( $value );
			}
			return $value;
		}
	}

	new Clover_Game_HighLow();

endif;
