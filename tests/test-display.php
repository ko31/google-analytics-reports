<?php
/**
 * Class BogoDate_Display_Test
 *
 * @package BogoDate
 */

class BogoDate_Display_Test extends WP_UnitTestCase
{
    public function test_get_the_date()
    {
        $post = $this->factory()->post->create_and_get( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( $post->ID ) );
        $this->assertSame( get_the_date(), get_the_date( get_option( 'date_format' ) ) );

        update_option( BogoDate::get_instance()->get_prefix(), array(
            'date_format_' . get_locale() => 'Y/m/d',
        ) );
        $this->assertSame( get_the_date(), get_the_date( 'Y/m/d' ) );
    }

    public function test_get_the_time()
    {
        $post = $this->factory()->post->create_and_get( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( $post->ID ) );
        $this->assertSame( get_the_time(), get_the_time( get_option( 'time_format' ) ) );

        update_option( BogoDate::get_instance()->get_prefix(), array(
            'time_format_' . get_locale() => 'H:i:s',
        ) );
        $this->assertSame( get_the_time(), get_the_time( 'H:i:s' ) );
    }

    public function test_get_the_modified_date()
    {
        $post = $this->factory()->post->create_and_get( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( $post->ID ) );
		$this->assertSame(
			get_the_modified_date(),
			get_the_modified_date( get_option( 'date_format' ) )
		);

        update_option( BogoDate::get_instance()->get_prefix(), array(
            'date_format_' . get_locale() => 'Y/m/d',
        ) );
        $this->assertSame(
			get_the_modified_date(),
			get_the_modified_date( 'Y/m/d' )
		);
    }

    public function test_get_the_modified_time()
    {
        $post = $this->factory()->post->create_and_get( array( 'post_type' => 'post' ) );

		$this->go_to( get_permalink( $post->ID ) );
        $this->assertSame(
			get_the_modified_time(),
			get_the_modified_time( get_option( 'time_format' ) )
		);

        update_option( BogoDate::get_instance()->get_prefix(), array(
            'time_format_' . get_locale() => 'H:i:s',
        ) );
        $this->assertSame(
			get_the_modified_time(),
			get_the_modified_time( 'H:i:s' )
		);
    }
}
