<?php
// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date
// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
namespace FormVibes\Integrations;

use FormVibes\Classes\ApiEndpoint;
use FormVibes\Classes\DbManager;
use FormVibes\Classes\Utils;
use FormVibes\Pro\Classes\Settings;
use function GuzzleHttp\Promise\all;

/**
 * Caldera plugin class
 *
 * Register the Caldera plugin
 */

class Caldera extends Base {


	/**
	 * The instance of the class.
	 * @var null|object
	 *
	 */
	private static $instance = null;

	/**
	 * The instaciator of the class.
	 *
	 * @access public
	 * @since 1.4.4
	 * @return @var $instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * The constructor of the class.
	 *
	 * @access private
	 * @since 1.4.4
	 * @return void
	 */
	public function __construct() {
		$this->plugin_name = 'caldera';
		add_filter( 'fv_forms', [ $this, 'register_form' ] );

		add_filter( 'formvibes/forms', [ $this, 'forms' ] );
	}

	/**
	 * Get the analytics data for Caldera forms
	 *
	 * @param array $params The params.
	 * @access public
	 * @return array
	 */
	public function get_analytics( $params ) {

		// TODO:: Get time zone from utils
		$gmt_offset = get_option( 'gmt_offset' );
		$hours      = (int) $gmt_offset;
		$minutes    = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if ( $hours >= 0 ) {
			$time_zone = $hours . ':' . $minutes;
		} else {
			$time_zone = $hours . ':' . $minutes;
		}

		$filter_type = $params['filter_type'];

		$plugin_name = $params['plugin'];
		$from_date   = $params['fromDate'];
		$to_date     = $params['toDate'];
		$filter      = '';
		$formid      = $params['formid'];
		$label       = '';
		$query_param = '';
		$query_type  = $params['query_type'];

		if ( 'day' === $filter_type ) {
			$default_data = self::getDatesFromRange( $from_date, $to_date );
			$filter       = '%j';
			$label        = "MAKEDATE(DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y'), DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%j'))";
		} elseif ( 'month' === $filter_type ) {
			$default_data = self::get_month_range( $from_date, $to_date );
			$filter       = '%b';
			$label        = "concat(DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%b'),'(',DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%y'),')')";
		} else {
			$default_data = self::get_date_range_for_all_weeks( $from_date, $to_date );

			$start_week = get_option( 'start_of_week' );
			// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( 0 == $start_week ) {
				$filter      = '%U';
				$day_start   = 'Sunday';
				$week_number = '';
			} else {
				$filter      = '%u';
				$day_start   = 'Monday';
				$week_number = '';
			}
			$label = "STR_TO_DATE(CONCAT(DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y'),' ', DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '" . $filter . "')" . $week_number . ",' ', '" . $day_start . "'), '%X %V %W')";
		}
		if ( '%b' === $filter ) {
			$orderby = '%m';
		} else {
			$orderby = $filter;
		}
		global $wpdb;
		$query_param .= ' Where ';
		if ( $query_type !== 'All_Time' ) {
			$query_param .= " DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y-%m-%d') >= '" . $from_date . "'";
			$query_param .= " and DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y-%m-%d') <= '" . $to_date . "' and";
		}
		$query_param .= " form_id='" . $formid . "'";
		$data_query   = 'SELECT ' . $label . " as Label, CONCAT(DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '" . $filter . "'),'(',DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%y'),')') as week, count(*) as count,CONCAT(DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%y'),'-',DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '" . $orderby . "')) as ordering from {$wpdb->prefix}cf_form_entries " . $query_param . " GROUP BY DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '" . $orderby . "'),ordering ORDER BY ordering";
		$res          = [];

		$res['data'] = $wpdb->get_results( $data_query, OBJECT_K );

		if ( count( (array) $res['data'] ) > 0 ) {
			$key = array_keys( $res['data'] )[0];
			// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( null == $res['data'][ $key ]->Label || '' == $res['data'][ $key ]->Label ) {
				$abc                                   = [];
				$abc[ array_keys( $default_data )[0] ] = (object) $res['data'][''];

				$res['data'] = $abc + $res['data'];

				$res['data'][ array_keys( $default_data )[0] ]->Label = array_keys( $default_data )[0];
				unset( $res['data'][''] );
			}
		}
		$data = array_replace( $default_data, $res['data'] );
		if ( Utils::key_exists( 'dashboard_data', $params ) && $params['dashboard_data'] ) {
			$dashboard_data         = $this->prepare_data_for_dashboard_widget( $params, $res );
			$data['dashboard_data'] = $dashboard_data;
		}
		return $data;
	}

	/**
	 * Get the dashboard analytics data for Caldera forms
	 *
	 * @param array $params The params.
	 * @param array $res The results.
	 * @access public
	 * @return array
	 */
	private function prepare_data_for_dashboard_widget( $params, $res ) {
		// TODO:: Get time zone from utils
		$gmt_offset = get_option( 'gmt_offset' );
		$hours      = (int) $gmt_offset;
		$minutes    = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if ( $hours >= 0 ) {
			$time_zone = $hours . ':' . $minutes;
		} else {
			$time_zone = $hours . ':' . $minutes;
		}

		$all_forms      = [];
		$dashboard_data = [];
		$count          = count( $params['allForms'] );
		for ( $i = 0; $i < $count; ++$i ) {
			$plugin = $params['allForms'][ $i ]->label;
			$count  = count( $params['allForms'][ $i ]->options );
			for ( $j = 0; $j < $count; ++$j ) {
				$id               = $params['allForms'][ $i ]->options[ $j ]->value;
				$form_name        = $params['allForms'][ $i ]->options[ $j ]->label;
				$all_forms[ $id ] = [
					'id'       => $id,
					'plugin'   => $plugin,
					'formName' => $form_name,
				];
			}
		}
		if ( 'Last_7_Days' === $params['query_type'] || 'This_Week' === $params['query_type'] ) {
			$pre_from_date = date( 'Y-m-d', strtotime( $params['fromDate'] . '-7 days' ) );
			$pre_to_date   = date( 'Y-m-d', strtotime( $params['fromDate'] . '-1 days' ) );
		} elseif ( 'Last_30_Days' === $params['query_type'] ) {
			$pre_from_date = date( 'Y-m-d', strtotime( $params['fromDate'] . '-30 days' ) );
			$pre_to_date   = date( 'Y-m-d', strtotime( $params['fromDate'] . '-1 days' ) );
		} else {
			$pre_from_date = date( 'Y-m-01', strtotime( 'first day of last month' ) );
			$pre_to_date   = date( 'Y-m-t', strtotime( 'last day of last month' ) );
		}
		global $wpdb;
		$pre_param  = " where form_id='" . $params['formid'] . "' and DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y-%m-%d') >= '" . $pre_from_date . "'";
		$pre_param .= " and DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y-%m-%d') <= '" . $pre_to_date . "'";
		$qry        = "SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entries " . $pre_param;

		$pre_data_count = $wpdb->get_var( $qry );
		// get all forms data count.
		$param = '';
		foreach ( $all_forms as $form_key => $form_value ) {
			if ( 'Caldera' === $form_value['plugin'] || 'caldera' === $form_value['plugin'] ) {
				$param  = " where form_id='" . $form_key . "' and DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y-%m-%d') >= '" . $params['fromDate'] . "'";
				$param .= " and DATE_FORMAT(ADDTIME(datestamp,'" . $time_zone . "' ), '%Y-%m-%d') <= '" . $params['toDate'] . "'";
				$qry    = "SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entries " . $param;

				$data_count = $wpdb->get_var( $qry );

				$dashboard_data['allFormsDataCount'][ $form_key ] = [
					'plugin'   => $form_value['plugin'],
					'count'    => $data_count,
					'formName' => $form_value['formName'],
				];
			} else {
				$param  = " where form_id='" . $form_key . "' and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '" . $params['fromDate'] . "'";
				$param .= " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '" . $params['toDate'] . "'";
				$qry    = "SELECT COUNT(*) FROM {$wpdb->prefix}fv_enteries " . $param;

				$data_count = $wpdb->get_var( $qry );

				$dashboard_data['allFormsDataCount'][ $form_key ] = [
					'plugin'   => $form_value['plugin'],
					'count'    => $data_count,
					'formName' => $form_value['formName'],
				];
			}
		}
		$total_entries = 0;

		foreach ( $res['data'] as $key => $val ) {
			$total_entries += $val->count;
		}
		$dashboard_widget_setting               = [];
		$dashboard_widget_setting['query_type'] = $params['query_type'];
		$dashboard_widget_setting['plugin']     = $params['plugin'];
		$dashboard_widget_setting['formid']     = $params['formid'];
		update_option( 'fv_dashboard_widget_settings', $dashboard_widget_setting );
		$dashboard_data['totalEntries']               = $total_entries;
		$dashboard_data['previousDateRangeDataCount'] = $pre_data_count;
		return $dashboard_data;
	}


	/**
	 * Get the date range
	 *
	 * @param string $start The start date.
	 * @param string $end The end date.
	 * @access public
	 * @return array
	 */
	public static function getDatesFromRange( $start, $end, $format = 'Y-m-d' ) {

		$date_1 = $start;
		$date_2 = $end;

		$array = [];
		if ( '' === $date_1 && '' === $date_2 ) {
			return [];
		}
		// Use strtotime function
		$variable_1 = strtotime( $date_1 );
		$variable_2 = strtotime( $date_2 );

		// Use for loop to store dates into array
		// 86400 sec = 24 hrs = 60*60*24 = 1 day
		for (
			$current_date = $variable_1;
			$current_date <= $variable_2;
			$current_date += ( 86400 )
		) {

			$store = date( 'Y-m-d', $current_date );

			$array[ $store ] = (object) [
				'Label'    => $store,
				'week'     => ( date( 'z', $current_date ) + 1 ) . '(' . date( 'y', $current_date ) . ')',
				'count'    => 0,
				'ordering' => date( 'y', $current_date ) . '-' . ( date( 'z', $current_date ) + 1 ),
			];
		}
		$array[] = new \stdClass();
		unset( $array[0] );
		return $array;
	}

	/**
	 * Get the month range for the given range
	 *
	 * @param string $start The start date.
	 * @param string $end The end date.
	 * @access public
	 * @return array
	 */
	public static function get_month_range( $start_date, $end_date ) {
		if ( '' === $start_date && '' === $end_date ) {
			return [];
		}
		$start = new \DateTime( $start_date );
		$start->modify( 'first day of this month' );
		$end = new \DateTime( $end_date );
		$end->modify( 'first day of next month' );
		$interval = \DateInterval::createFromDateString( '1 month' );
		$period   = new \DatePeriod( $start, $interval, $end );

		$months = [];
		foreach ( $period as $dt ) {
			$months[ $dt->format( 'M' ) . '(' . $dt->format( 'y' ) . ')' ] = (object) [
				'Label'    => $dt->format( 'M' ) . '(' . $dt->format( 'y' ) . ')',
				'week'     => '',
				'count'    => 0,
				'ordering' => '',
			];
		}

		return $months;
	}

	/**
	 * Get the date range for all weeks
	 *
	 * @param string $start The start date.
	 * @param string $end The end date.
	 * @access public
	 * @return array
	 */
	public static function get_date_range_for_all_weeks( $start, $end ) {
		if ( '' === $start && '' === $end ) {
			return [];
		}
		$fweek = self::get_date_range_for_week( $start );
		$lweek = self::get_date_range_for_week( $end );

		$week_dates = [];

		while ( $fweek['sunday'] < $lweek['sunday'] ) {
			$week_dates[ $fweek['monday'] ] = (object) [
				'Label'    => $fweek['monday'],
				'week'     => '',
				'count'    => 0,
				'ordering' => '',
			];

			$date = new \DateTime( $fweek['sunday'] );
			$date->modify( 'next day' );

			$fweek = self::get_date_range_for_week( $date->format( 'Y-m-d' ) );
		}
		$week_dates[ $lweek['monday'] ] = (object) [
			'Label'    => $lweek['monday'],
			'week'     => '',
			'count'    => 0,
			'ordering' => '',
		];

		return $week_dates;
	}

	/**
	 * Get the date range for a week
	 *
	 * @param string $start The date.
	 * @access public
	 * @return array
	 */
	public static function get_date_range_for_week( $date ) {
		$date_time = new \DateTime( $date );

		if ( 'Monday' === $date_time->format( 'l' ) ) {
			$monday = date( 'Y-m-d', strtotime( $date ) );
		} else {
			$monday = date( 'Y-m-d', strtotime( 'last monday', strtotime( $date ) ) );
		}

		$sunday = 'Sunday' === $date_time->format( 'l' ) ? date( 'Y-m-d', strtotime( $date ) ) : date( 'Y-m-d', strtotime( 'next sunday', strtotime( $date ) ) );
		return [
			'monday' => $monday,
			'sunday' => $sunday,
		];
	}
	/**
	 * Register the form plugin
	 *
	 * @param array $forms
	 * @access public
	 * @return array
	 */
	public function register_form( $forms ) {
		$forms[ $this->plugin_name ] = 'Caldera';
		return $forms;
	}

	/**
	 * Get forms
	 *
	 * @param array $param
	 * @access public
	 * @return array
	 */
	public static function get_forms( $param = [] ) {

		$post_type = $param;

		global $wpdb;

		$form_result = $wpdb->get_results( $wpdb->prepare( "select * from {$wpdb->prefix}cf_forms where type=%s", 'primary' ) );
		$data        = [];
		foreach ( $form_result as $form ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$form_name                = unserialize( $form->config );
			$data[ $form_name['ID'] ] = [
				'id'   => $form_name['ID'],
				'name' => $form_name['name'],
			];
		}
		return $data;
	}

	/**
	 * Get caldera forms
	 *
	 * @param array $forms
	 * @access public
	 * @return array
	 */
	public function forms( $forms ) {

		$cf_forms = self::get_forms();

		$forms[ $this->plugin_name ] = $cf_forms;

		return $forms;
	}
}
