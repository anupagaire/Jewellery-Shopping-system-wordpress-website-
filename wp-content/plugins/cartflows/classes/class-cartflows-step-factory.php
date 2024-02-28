<?php
/**
 * Get current step data - factory.
 *
 * @package cartflows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Step factory
 *
 * @since 1.0.0
 */
class Cartflows_Step_Factory {

	/**
	 * Member Variable
	 *
	 * @var int step_id
	 */
	private $step_id;

	/**
	 * Member Variable
	 *
	 * @var int control_step_id
	 */
	private $control_step_id;

	/**
	 * Member Variable
	 *
	 * @var int flow_id
	 */
	private $flow_id;


	/**
	 * Member Variable
	 *
	 * @var string step_type
	 */
	private $step_type;

	/**
	 * Member Variable
	 *
	 * @var int flow_steps
	 */
	private $flow_steps;

	/**
	 * Member Variable
	 *
	 * @var int flow_steps
	 */
	private $flow_steps_map;

	/**
	 * Member Variable
	 *
	 * @var int ab_test
	 */
	private $ab_test;

	/**
	 * Member Variable
	 *
	 * @var int all_variations
	 */
	private $all_variations;

	/**
	 * Member Variable
	 *
	 * @var array show_variations
	 */
	private $show_variations;

	/**
	 * Member Variable
	 *
	 * @var int show_variation_id
	 */
	private $show_variation_id;

	/**
	 *  Constructor
	 *
	 * @param int $id step id.
	 */
	public function __construct( $id = null ) {

		if ( null !== $id ) {

			/* Data from step */
			$this->step_id   = intval( $id );
			$this->step_type = get_post_meta( $this->step_id, 'wcf-step-type', true );
			/* Data from flow */
			$this->flow_id    = get_post_meta( $this->step_id, 'wcf-flow-id', true );
			$this->flow_steps = get_post_meta( $this->flow_id, 'wcf-steps', true );

			/* If Ab test */
			$this->ab_test         = get_post_meta( $this->step_id, 'wcf-ab-test', true );
			$this->control_step_id = $this->step_id;

			if ( $this->ab_test ) {

				$control_step_id = get_post_meta( $this->step_id, 'wcf-control-step', true );

				if ( ! empty( $control_step_id ) ) {
					$this->control_step_id = intval( $control_step_id );
				}
			}
		}
	}

	/**
	 * Is ab test enable
	 */
	public function is_ab_test_enable() {

		if ( $this->ab_test ) {

			return true;
		}

		return false;
	}

	/**
	 * Get flow id
	 */
	public function get_flow_id() {
		return $this->flow_id;
	}

	/**
	 * Get step id
	 */
	public function get_step_id() {
		return $this->step_id;
	}

	/**
	 * Get step type
	 */
	public function get_step_type() {
		return $this->step_type;
	}

	/**
	 * Get control step id
	 */
	public function get_current_step() {
		return $this->step_id;
	}

	/**
	 * Get control step id
	 */
	public function get_control_step() {
		return $this->control_step_id;
	}

	/**
	 * Get flow steps
	 */
	public function get_flow_steps() {
		return $this->flow_steps;
	}

	/**
	 * Set flow steps map
	 */
	public function set_flow_steps_map() {

	}

	/**
	 * Check for thank you page
	 *
	 * @return bool
	 */
	public function is_thankyou_page() {

		$step_type = $this->get_step_type();

		if ( 'thankyou' === $step_type ) {

			return true;
		}

		return false;
	}

	/**
	 * Check for checkout page
	 *
	 * @return bool
	 */
	public function is_checkout_page() {

		$step_type = $this->get_step_type();

		if ( 'checkout' === $step_type ) {

			return true;
		}

		return false;
	}

	/**
	 * Get direct next step id.
	 *
	 * @since 1.6.15
	 *
	 * @return bool|int
	 */
	public function get_direct_next_step_id() {

		$next_step_id = false;

		if ( $this->flow_id ) {

			$flow_steps   = $this->get_flow_steps();
			$control_step = $this->get_control_step();

			if ( is_array( $flow_steps ) ) {

				foreach ( $flow_steps as $index => $data ) {

					if ( intval( $data['id'] ) === $control_step ) {

						$next_step_index = $index + 1;

						if ( isset( $flow_steps[ $next_step_index ] ) ) {

							$next_step_id = intval( $flow_steps[ $next_step_index ]['id'] );
						}

						break;
					}
				}
			}
		}

		return $next_step_id;
	}
}
