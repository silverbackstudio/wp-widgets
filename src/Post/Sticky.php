<?php

namespace Svbk\WP\Widgets\Post;


class Sticky extends Latest {

	public $id_base = 'svbk_post_sticky';

	protected function title() {
		return __( 'Sticky Post', 'svbk-widgets' );
	}

	protected function args() {
		return array(
			'description' => __( '', 'svbk-widgets' ),
		);
	}

	public function queryArgs() {

		$query_args = parent::queryArgs();

		$query_args['post__in'] = get_option( 'sticky_posts' );
		$query_args['ignore_sticky_posts'] = 1;

		return $query_args;
	}

}
