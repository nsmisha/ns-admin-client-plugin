<?php

class NS_Client_Task_Settings {
	/**
	 * Required php version 5.6 and more for the const array
	 */
	const PRIORITY = [
		'86400'   => '1 Day',
		'259200'  => '3 Day',
		'604800'  => '1 Week',
		'1209600' => '2 Weeks',
		'2419200' => '1 Month +',
	];
	const PREFIX = 'ns_';
	const STATUS_PENDING = 'pending';
	const STATUS_REQUEST = 'request';
	const STATUS_FIXED_STAGING = 'fixed_on_staging';
	const STATUS_APPR_STAGING = 'appr_on_staging';
	const STATUS_PUSHED_TO_LIVE = 'pushed_to_live';
	const STATUS_APPROVED_ON_LIVE = 'approved_on_live';
	const STATUS_CLOSED = 'closed';
	
	const STATUSES = [
		self::STATUS_PENDING,
		self::STATUS_REQUEST,
		self::STATUS_FIXED_STAGING,
		self::STATUS_APPR_STAGING,
		self::STATUS_PUSHED_TO_LIVE,
		self::STATUS_APPROVED_ON_LIVE,
		self::STATUS_CLOSED,
	];
	
	const TASK_ORDER_BY_OPTION = 'ns_complete_order_by';
	const TASK_ORDER_OPTION = 'ns_complete_order';
	const TASK_DEFAULT_ORDER_BY = 'menu_order';
	const TASK_DEFAULT_ORDER = 'desc';
	
	/**
	 * Register additional statuses
	 * @return void
	 */
	public function register_statuses() {
		foreach ( static::STATUSES as $status ) {
			$prepared_name = ucfirst( str_replace( '_', ' ', $status ) );
			register_post_status( static::PREFIX . $status, [
				'label'                     => _x( $prepared_name, 'post' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( $prepared_name . ' <span class="count">(%s)</span>', $prepared_name . ' <span class="count">(%s)</span>' ),
			] );
		}
	}
	
	/**
	 * Get task priority list
	 *
	 * @return array
	 */
	public function get_priority_list() {
		return static::PRIORITY;
	}
	
	/**
	 * Get default status
	 *
	 * @return string
	 */
	public function get_default_status() {
		return static::PREFIX . static::STATUS_PENDING;
	}
	
	/**
	 * Check is status is default
	 *
	 * @param $status
	 *
	 * @return bool
	 */
	public function check_default_status( $status ) {
		return static::PREFIX . static::STATUS_PENDING === $status;
	}
	
	/**
	 * @return array
	 */
	public function get_request_statuses() {
		return [
			$this->get_default_status(),
			static::PREFIX . static::STATUS_REQUEST,
		];
	}
	
	/**
	 * @return array
	 */
	public function get_fixed_staging_statuses() {
		return [
			static::PREFIX . static::STATUS_FIXED_STAGING,
		];
	}
	
	/**
	 * @return array
	 */
	public function get_approved_on_staging_statuses() {
		return [
			static::PREFIX . static::STATUS_APPR_STAGING,
		];
	}
	
	/**
	 * @param $status
	 *
	 * @return bool
	 */
	public function is_staging( $status ) {
		return in_array( $status, array_merge( $this->get_fixed_staging_statuses(), $this->get_approved_on_staging_statuses() ) );
	}
	
	/**
	 * @return array
	 */
	public function get_pushed_live_statuses() {
		return [
			static::PREFIX . static::STATUS_PUSHED_TO_LIVE,
		];
	}
	
	/**
	 * @return array
	 */
	public function get_approved_live_statuses() {
		return [
			static::PREFIX . static::STATUS_APPROVED_ON_LIVE,
		];
	}
	
	/**
	 * @param $status
	 *
	 * @return bool
	 */
	public function is_live( $status ) {
		return in_array( $status, array_merge( $this->get_pushed_live_statuses(), $this->get_approved_live_statuses() ) );
	}
	
	/**
	 * @param $status
	 *
	 * @return bool
	 */
	public function is_aproved_on_live( $status ) {
		return in_array( $status, $this->get_approved_live_statuses() );
	}
	
	/**
	 * @param $status
	 *
	 * @return bool
	 */
	public function get_client_in_progress() {
		return array_merge( [ static::PREFIX . static::STATUS_PENDING ], $this->get_request_statuses(), $this->get_fixed_staging_statuses(), $this->get_approved_on_staging_statuses(), $this->get_pushed_live_statuses() );
	}
	
	/**
	 * Get task closed statuses
	 *
	 * @return array
	 */
	public function get_closed_statuses() {
		return [
			static::PREFIX . static::STATUS_CLOSED,
		];
	}
	
	/**
	 * @param $status
	 *
	 * @return bool
	 */
	public function is_closed( $status ) {
		return in_array( $status, $this->get_closed_statuses() );
	}
	
	/**
	 * @param $status
	 *
	 * @return bool
	 */
	public function is_available_for_client( $status ) {
		return in_array( $status, array_merge( $this->get_approved_on_staging_statuses(), $this->get_approved_live_statuses(), $this->get_closed_statuses(), $this->get_pushed_live_statuses() ) );
	}
	
	/**
	 * @param $status
	 *
	 * @return string
	 */
	public function get_state( $status ) {
		$state = '';
		
		if ( $this->is_live( $status ) ) {
			$state = 'pushed_to_live';
		} elseif ( $this->is_staging( $status ) ) {
			$state = 'fixed_on_staging';
		} elseif ( $this->is_closed( $status ) ) {
			$state = 'closed';
		} elseif ( $this->check_default_status( $status ) ) {
			$state = 'pending';
		}
		
		return $state;
	}
	
	/**
	 * @return array
	 */
	public function get_statuses() {
		return array_map( function ( $status ) {
			return static::PREFIX . $status;
		}, static::STATUSES );
	}
	
	/**
	 * Remove prefix from status
	 *
	 * @param $status
	 *
	 * @return mixed
	 */
	public function exclude_prefix( $status ) {
		return str_replace( static::PREFIX, '', $status );
	}
	
	/**
	 * @param $status
	 *
	 * @return string
	 */
	public function prepare_status( $status ) {
		return ucfirst( str_replace( '_', ' ', $this->exclude_prefix( $status ) ) );
	}
	
	/**
	 * @param $task
	 *
	 * @return bool
	 */
	public function check_private( $task ) {
		return ! empty( get_post_meta( $task->ID, '_private', true ) );
	}
	
	/**
	 * Get option order by key
	 *
	 * @return string
	 */
	public function complete_order_by_option_key() {
		return static::TASK_ORDER_BY_OPTION;
	}
	
	/**
	 * Get oprton order key
	 *
	 * @return string
	 */
	public function complete_order_option_key() {
		return static::TASK_ORDER_OPTION;
	}
	
	/**
	 * Get default value
	 *
	 * @return string
	 */
	public function default_order_by_value() {
		return static::TASK_DEFAULT_ORDER_BY;
	}
	
	/**
	 * Get default value
	 *
	 * @return string
	 */
	public function default_order_value() {
		return static::TASK_DEFAULT_ORDER;
	}
}