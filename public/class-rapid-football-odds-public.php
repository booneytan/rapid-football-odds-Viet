<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://localhost.localdomain
 * @since      1.0.0
 *
 * @package    Rapid_Football_Odds
 * @subpackage Rapid_Football_Odds/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Rapid_Football_Odds
 * @subpackage Rapid_Football_Odds/public
 * @author     Dev Team <dev@localhost.localdomain>
 */
class Rapid_Football_Odds_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rapid_Football_Odds_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rapid_Football_Odds_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rapid-football-odds-new.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rapid_Football_Odds_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rapid_Football_Odds_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rapid-football-odds-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/script.js', array(), $this->version, true );

	}

	public function clear_cache() {
		$files = glob(dirname(__FILE__) . '/cache/*'); // get all file names
		foreach ($files as $file) { // iterate files
			if (is_file($file)) {
				unlink($file); // delete file
			}
		}
	}

	public function live_results_function($atts, $cache_file = NULL, $expires = NULL) {
		$a = shortcode_atts( array(
			'href'  =>  '#',
			'league'  =>  '',
			'show' => '2',
			'league_title' => 'true',
			'logo' => 'true',
			'goals' => 'true',
			'status' => 'true',
			'reverse' => 'false',
    ), $atts );

	$cache_file = dirname(__FILE__) . '/cache/default-api-cache-show-' . $a['show'] . '-with-league-' . $a['league'] . '-and-logo-' . $a['logo'] . '.txt';
	$expires = time() - 4*60*60;

	if( !file_exists($cache_file) ) fopen($cache_file, "rw");
	// Check that the file is older than the expire time and that it's not empty
	if ( filectime($cache_file) < $expires || file_get_contents($cache_file)  == '' ) {
		$all_response = '<div class="odds-section">';
		if ($a['league'] != '') {
			$page_total = $this->get_odds_league_page_total($a['league']);
			$curr = (array) null;

			for ($i = 1; $i <= $page_total; $i++) {
				if (empty($curr)) {
					$curr = $this->get_odds_by_league($a['league'], $i);
				} else {
					$temp = $this->get_odds_by_league($a['league'], $i);
					$curr = array_merge($curr, $temp);
					$odds = $curr;
				}
			}
		} else {
			$first = $this->get_odds_by_page(1);
			$second = $this-> get_odds_by_page(2);
			$odds = array_merge($first, $second);
		}

		$league_id = '1';

		// Obtain a list of odds
		foreach ($odds as $key => $row) { 
			$odds_league_id[$key]  = $row->league->id; 
			$odds_fixture_date[$key] = $row->fixture->date;
		}
		// Sort the data with league id descending, fixture date ascending
		array_multisort($odds_league_id, SORT_ASC, $odds_fixture_date, SORT_ASC, $odds);

		if ($a['reverse'] == 'true') {
			$odds = array_reverse($odds);
		}
		
		$count = 0;

		foreach ($odds as $odd) {

			$fixture_id = $odd->fixture->id;
			$fixture = $this->get_fixture_by_id($fixture_id)[0];
			$data_date = date("Y-m-d", strtotime($fixture->fixture->date));

			if($data_date >= date("Y-m-d")) {
				$response = '<div class="odds-div"><table class="odds-table">';
				if ($a['league_title'] == 'true') {
				$response .= '<tr><div class="league-logo-name">';
				if ($a['logo'] == 'true') {
					$response .= '<img class="league-logo" width="30" height="30" src="' . $fixture->league->logo . '"  /> ';
				}
				$response .= $fixture->league->name  . '</div></tr>';
				}

				$response .= '<tr>';
				$response .= '<td><div class="team-name-logo-left">';

				if ($a['logo'] == 'true') {
					$response .= '<div class="team-logo"><img width="30" height="30" src="' . $fixture->teams->home->logo . '" /></div> ';
				}

				$response .= '<p>' . $fixture->teams->home->name . '</p></div></td>';

				if ($a['goals'] == 'true') {
					$response .= '<td><p class="home-goal">' . $fixture->goals->home . '</p></td>';
					$response .= '<td>' . date("F j Y", strtotime($fixture->fixture->date)) . '<br />' . date("G:i a", strtotime($fixture->fixture->date)) . '</td>';
					$response .= '<td><p class="away-goal">' . $fixture->goals->away . '</p></td>';
				} else {
					$response .= '<td>' . date("F j", strtotime($fixture->fixture->date)) . '<br />' . date("G:i a", strtotime($fixture->fixture->date)) . '</td>';
				}
				$response .= '<td><div class="team-name-logo-right">';
				if ($a['logo'] == 'true') {
					$response .= '<div class="team-logo"><img width="30" height="30" src="' . $fixture->teams->away->logo . '" /></div> ';
				}

				$response .= '<p>' . $fixture->teams->away->name . '</p></div></td>';
				$response .= '</tr>';
				$response .= $r;
				$response .= '</table>';

				if ($a['status'] == 'true') {
					$response .= '<p class="status">' . $fixture->fixture->status->long . '</p>';
				}

				$response .= '<a class="odds-button" target="_blank" href="' . $a['href'] . '">ĐẶT CƯỢC NGAY</a></div>';
				
				$league_id = $fixture->league->id;
				
				$all_response .= $response;
				$count++;

				if ($count == $a['show']) {
					break;
				}
			}
		}
		$all_response .= '</div>';
      file_put_contents($cache_file, $all_response);
    } else {
        $all_response = file_get_contents($cache_file);
    }
		return $all_response;
	}

	public function box_odds_function($atts, $cache_file = NULL, $expires = NULL) {
		$a = shortcode_atts( array(
			'href'  =>  '#',
			'league'  =>  '',
			'show' => '2',
			'league_title' => 'flase',
			'logo' => 'true',
			'reverse' => 'false',
			'slider' => 'false',
			'goals' => 'false',
			'bets' => 'true',
			'status' => 'false',
    ), $atts );

	$cache_file = dirname(__FILE__) . '/cache/default-api-cache-show-' . $a['show'] . '-with-league-' . $a['league'] . '-and-logo-' . $a['logo'] . '-in-reverse-' . $a['reverse'] . '.txt';
	$expires = time() - 4*60*60;

	if( !file_exists($cache_file) ) fopen($cache_file, "rw");
	// Check that the file is older than the expire time and that it's not empty
	if ( filectime($cache_file) < $expires || file_get_contents($cache_file)  == '' ) {
		if ($a['slider'] == 'true') {
			$all_response = '<div class="odds-section slider-wrapper"><div class="slider-nav">
			<span id="prev"> < </span>
			<span id="next">></span>
			</div><div class="slider">';
		} else {
			$all_response = '<div class="odds-section">';
		}

		if ($a['league'] != '') {
			$page_total = $this->get_odds_league_page_total($a['league']);
			$curr = (array) null;

			for ($i = 1; $i <= $page_total; $i++) {
				if (empty($curr)) {
					$curr = $this->get_odds_by_league($a['league'], $i);
				} else {
					$temp = $this->get_odds_by_league($a['league'], $i);
					$curr = array_merge($curr, $temp);
					$odds = $curr;
				}
			}
		} else {
			$first = $this->get_odds_by_page(1);
			$second = $this-> get_odds_by_page(2);
			$odds = array_merge($first, $second);
		}

		// $league_id = '1';
		echo $a['league'];

		// Obtain a list of odds
		foreach ($odds as $key => $row) { 
			$odds_league_id[$key]  = $row->league->id; 
			$odds_fixture_date[$key] = $row->fixture->date;
		}
		// Sort the data with league id descending, fixture date ascending
		array_multisort($odds_league_id, SORT_ASC, $odds_fixture_date, SORT_ASC, $odds);

		if ($a['reverse'] == 'true') {
			$odds = array_reverse($odds);
		}
		
		$count = 0;

		foreach ($odds as $odd) {
			$r = '';
			if ($a['bets'] == 'true') {
				$r .= '<tr class="odds-values-row">';
				foreach ($odd->bookmakers[0]->bets[0]->values as $value) {
					if ($a['goals'] == 'true') {
						$r .= '<td colspan="2">';
					} else {
						$r .= '<td>';
					}

					switch ($value->value) {
						case 'Home' : $r .= 'Đội nhà '; break;
						case 'Draw' : $r .= 'Hòa '; break;
						case 'Away' : $r .= 'Đội khách '; break;
						default : '';
					}
					$r .= $value->odd . '</td>';
				}
				$r .= '</tr>';
			}

			$fixture_id = $odd->fixture->id;
			$fixture = $this->get_fixture_by_id($fixture_id)[0];
			$data_date = date("Y-m-d", strtotime($fixture->fixture->date));

			if($data_date >= date("Y-m-d")) {
				if ($a['slider'] == 'true') {
					$response = '<div class="odds-div browser"><table class="odds-table">';
				} else {
					$response = '<div class="odds-div"><table class="odds-table">';
				}

				if ($a['league_title'] == 'true') {
				$response .= '<tr><div class="league-logo-name">';
				if ($a['logo'] == 'true') {
					$response .= '<img class="league-logo" width="30" height="30" src="' . $fixture->league->logo . '"  /> ';
				}
				$response .= $fixture->league->name  . '</div></tr>';
				}

				$response .= '<tr>';
				$response .= '<td><div class="team-name-logo-left">';

				if ($a['logo'] == 'true') {
					$response .= '<div class="team-logo"><img width="30" height="30" src="' . $fixture->teams->home->logo . '" /></div> ';
				}

				$response .= '<p>' . $fixture->teams->home->name . '</p></div></td>';

				if ($a['goals'] == 'true') {
					$response .= '<td><p class="home-goal">' . $fixture->goals->home . '</p></td>';
					$response .= '<td>' . date("F j Y", strtotime($fixture->fixture->date)) . '<br />' . date("G:i a", strtotime($fixture->fixture->date)) . '</td>';
					$response .= '<td><p class="away-goal">' . $fixture->goals->away . '</p></td>';
				} else {
					$response .= '<td>' . date("F j", strtotime($fixture->fixture->date)) . '<br />' . date("G:i a", strtotime($fixture->fixture->date)) . '</td>';
				}
				$response .= '<td><div class="team-name-logo-right">';
				if ($a['logo'] == 'true') {
					$response .= '<div class="team-logo"><img width="30" height="30" src="' . $fixture->teams->away->logo . '" /></div> ';
				}

				$response .= '<p>' . $fixture->teams->away->name . '</p></div></td>';
				$response .= '</tr>';
				$response .= $r;
				$response .= '</table>';

				if ($a['status'] == 'true') {
					$response .= '<p class="status">' . $fixture->fixture->status->long . '</p>';
				}

				$response .= '<a class="odds-button" target="_blank" href="' . $a['href'] . '">ĐẶT CƯỢC NGAY</a></div>';
				
				$league_id = $fixture->league->id;
			
				$all_response .= $response;
				$count++;

				if ($count == $a['show']) {
					break;
				}
			}
		}
		$all_response .= '</div>';
      file_put_contents($cache_file, $all_response);
    } else {
        $all_response = file_get_contents($cache_file);
    }
		return $all_response;
	}

	public function table_function($atts, $cache_file = NULL, $expires = NULL) {
		$a = shortcode_atts( array(
			'href'  =>  '#',
			'league'  =>  '',
    ), $atts );

		$cache_file = dirname(__FILE__) . '/cache/table-api-cache-with-league-' . $a['league'] .'.txt';
		$expires = time() - 12*60*60;

		if( !file_exists($cache_file) ) fopen($cache_file, "rw");
		// Check that the file is older than the expire time and that it's not empty
		if ( filectime($cache_file) < $expires || file_get_contents($cache_file)  == '' ) {
			$all_response = '<div class="table-odds"><table class="table-odds-section">';
			
			if ($a['league'] != '') {
				$page_total = $this->get_odds_league_page_total($a['league']);
				$curr = (array) null;

				for ($i = 1; $i <= $page_total; $i++) {
					if (empty($curr)) {
						$curr = $this->get_odds_by_league($a['league'], $i);
					} else {
						$temp = $this->get_odds_by_league($a['league'], $i);
						$curr = array_merge($curr, $temp);
						$odds = $curr;
					}
				}
			} else {
				$first = $this->get_odds_by_page(1);
				$second = $this-> get_odds_by_page(2);
				$odds = array_merge($first, $second);
			}

			$league_id = '1';

			// Obtain a list of odds
			foreach ($odds as $key => $row) { 
				$odds_league_id[$key]  = $row->league->id; 
				$odds_fixture_date[$key] = $row->fixture->date;
			}
			// Sort the data with league id descending, fixture date ascending
			array_multisort($odds_league_id, SORT_ASC, $odds_fixture_date, SORT_ASC, $odds);

			foreach ($odds as $odd) {
				foreach ($odd->bookmakers[0]->bets as $bet) {
					if ($bet->name == 'Match Winner') {
						$match_winner = '<td>';
						foreach ($bet->values as $value) {
							switch ($value->value) {
								case 'Home' : $match_winner .= 'Đội nhà '; break;
								case 'Draw' : $match_winner .= 'Hòa '; break;
								case 'Away' : $match_winner .= 'Đội khách '; break;
								default : '';
							}
							$match_winner .= ' : ' . $value->odd . '<br />';
						}
						$match_winner .= '</td>';
					} else if ($bet->name == 'Goals Over/Under') {
						$over_under = '<td>';
						foreach ($bet->values as $value) {
							switch (explode(' ',trim($value->value))[0]) {
								case 'Over' : $over_under .= str_replace('Over', 'Tài', $value->value); break;
								case 'Under' : $over_under .= str_replace('Under', 'Xỉu', $value->value); break;
								default : '';
							}
							$over_under .= ' : ' . $value->odd . '<br />';
						}
						$over_under .= '</td>';
					} else if ($bet->name == 'Handicap Result') {
						$handicap = '<td>';
						foreach ($bet->values as $value) {
							switch (explode(' ',trim($value->value))[0]) {
								case 'Home' : $handicap .= str_replace('Home', 'Đội nhà', $value->value); break;
								case 'Draw' : $handicap .= str_replace('Draw', 'Hòa', $value->value); break;
								case 'Away' : $handicap .= str_replace('Away', 'Đội khách', $value->value); break;
								default : '';
							}
							$handicap .= ' : ' . $value->odd . '<br />';
						}
						$handicap .= '</td>';
					} else if ($bet->name == 'Odd/Even') {
						$odd_even = '<td>';
						foreach ($bet->values as $value) {
							switch ($value->value) {
								case 'Odd' : $odd_even .= 'Chẵn'; break;
								case 'Even' : $odd_even .= 'Lẻ'; break;
								default: '';
							}
							$odd_even .= ' : ' . $value->odd . '<br />';
						}
						$odd_even .= '</td>';
					}
				}

				$fixture_id = $odd->fixture->id;
				$fixture = $this->get_fixture_by_id($fixture_id)[0];
				if ($league_id != $fixture->league->id) {
					$response = '<tr class="league-row">';
					$response .= '<table class="table-odds-section">';
					$response .= '<th colspan="6"><div>';
					$response .= '<img width="30" height="30" class="table-league-logo" src="' . $fixture->league->logo . '" />';
					$response .= $fixture->league->name . '</div></th></tr>';

					$response .= '<tr class="header-row">';
					$response .= '<td><b>Giờ</b></td>';
					$response .= '<td><b>Trận Đấu</b></td>';
					$response .= '<td><b>Kèo Tài Xỉu</b></td>';
					$response .= '<td><b>Kèo Chẵn Lẻ</b></td>';
					$response .= '<td><b>Kèo Châu Á</b></td>';
					$response .= '<td><b>1x2</b></td>';
					$response .= '</tr>';

					$league_id = $fixture->league->id;
				} else {
					$response = '';
				}

				$data_date = date("Y-m-d", strtotime($fixture->fixture->date));

				// if($data_date >= date("Y-m-d")) {
					$response .= '<tr class="match-row">';
					$response .= '<td>';
					$response .= '<div class="blk_teamlogo"><div class="teamlogo">';
					$response .= '<div class="team-logo"><img width="30" height="30" src="' . $fixture->teams->home->logo . '" /></div> ';
					//$response .= '<img class="team-logo" width="20" height="20" src="' . plugin_dir_url( __FILE__ ) . 'images/Red_user.png" /><br/>';
					$response .= $fixture->teams->home->name;
					$response .= '</div>';
					$response .= '<span style="padding: 0 20px;">vs</span>';
					$response .= '<div class="teamlogo">';
					$response .= '<div class="team-logo"><img width="30" height="30" src="' . $fixture->teams->away->logo . '" /></div> ';
					//$response .= '<img width="20" height="20" class="team-logo" src="' . plugin_dir_url( __FILE__ ) . 'images/Black_user.png" /><br/>';
					$response .= $fixture->teams->away->name;
					$response .= '</div></div>';
					$response .= '</td>';

					$response .= '<td><center>' . date("G:i a", strtotime($fixture->fixture->date));
					$response .= '<br />' . date("F d", strtotime($fixture->fixture->date)) . '</center></td>';

					if ($over_under != '') {
						$response .= $over_under;
					} else {
						$response .= '<td></td>';
					}
					
					if ($odd_even != '') {
						$response .= $odd_even;
					} else {
						$response .= '<td></td>';
					} 
					
					if ($handicap != '') {
						$response .= $handicap;
					} else {
						$response .= '<td></td>';
					}
					
					if ($match_winner != '') {
						$response .= $match_winner;
					} else {
						$response .='<td></td>';
					}
					$response .= '</tr>';
					$response .= '<tr class="button-row">';
					$response .= '<td colspan="6" padding="10px">';
					$response .= '<a class="odds-button" target="_blank" href="' . $a['href'] . '">ĐẶT CƯỢC NGAY</a>';
					$response .= '</td>';
					$response .= '</tr>';
				// }
				
				$all_response .= $response;
			}
			$all_response .= '</table></div>';
      file_put_contents($cache_file, $all_response);
    } else {
        $all_response = file_get_contents($cache_file);
    }
		return $all_response;
	}

	public function standings_function($atts) {
		$a = shortcode_atts( array(
			'league'  =>  '',
    ), $atts );
		$response = '';
		$response = '

		<div id="wg-api-football-standings"
		data-host="api-football-v1.p.rapidapi.com"
		data-refresh="60"
		data-league="'.$a['league'].'"
		data-season="2021"
		data-key="b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
		data-theme="white"
		data-show-errors="false"
		class="api_football_loader"></div>
		<script
				type="module"
				src="https://widgets.api-sports.io/football/1.1.8/widget.js">
		</script>';
		return $response;
	}

	public function livescore_function() {
		$response = '';
		$response = '
		<div 
			id="wg-api-football-fixtures"
			data-host="api-football-v1.p.rapidapi.com"
			data-refresh="60"
			data-date="' . date("Y-m-d") . '"
			data-key="b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
			data-theme="white"
			data-show-errors="false"
			class="api_football_loader">
		</div>
		<script
				type="module"
				src="https://widgets.api-sports.io/football/1.1.8/widget.js">
		</script>';
		return $response;
	}

	private function get_fixture_by_id($fixture_id) {
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api-football-v1.p.rapidapi.com/v3/fixtures?id=" . $fixture_id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"x-rapidapi-host: api-football-v1.p.rapidapi.com",
				"x-rapidapi-key: b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return json_decode($response)->response;
		}
	}

	private function get_odds_by_page($page_number) {
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api-football-v1.p.rapidapi.com/v3/odds?date=" . date("Y-m-d") . "&timezone=Asia%2FHo_Chi_Minh&page=" . $page_number,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"x-rapidapi-host: api-football-v1.p.rapidapi.com",
				"x-rapidapi-key: b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return json_decode($response)->response;
		}
	}

	private function get_odds_league_page_total($league_id) {
		$curl = curl_init();
		$season_year = date('Y');
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api-football-v1.p.rapidapi.com/v3/odds?league=" . $league_id . "&season=" . $season_year . "&timezone=Asia%2FHo_Chi_Minh",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"x-rapidapi-host: api-football-v1.p.rapidapi.com",
				"x-rapidapi-key: b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return json_decode($response)->paging->total;
		}
	}

	private function get_odds_by_league($league_id, $page_number) {
		$curl = curl_init();
		$season_year = date('Y');
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api-football-v1.p.rapidapi.com/v3/odds?league=" . $league_id . "&season=" . $season_year . "&timezone=Asia%2FHo_Chi_Minh&page=" . $page_number,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"x-rapidapi-host: api-football-v1.p.rapidapi.com",
				"x-rapidapi-key: b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return json_decode($response)->response;
		}
	}

	// Thai Lottery Latest Results
	public function lottery_function($atts, $cache_file = NULL, $expires = NULL) {
		$a = shortcode_atts( array(
			'href'  =>  '#',
    ), $atts );

	$cache_file = dirname(__FILE__) . '/cache/thai-lottery-table-api-cache.txt';
	$expires = time() - 1*60*60;

	$wpmlLang = "vn";
	if(ICL_LANGUAGE_CODE=='en') {
		$wpmlLang = "en";
	} else if(ICL_LANGUAGE_CODE=='th') {
		$wpmlLang = "th";
	}

	if( !file_exists($cache_file) ) fopen($cache_file, "rw");
	// Check that the file is older than the expire time and that it's not empty
		if ( filectime($cache_file) < $expires || file_get_contents($cache_file)  == '' ) {
			$lotteries = $this->get_thai_lottery($thailottery);
			$obj = json_decode($lotteries);
			// print("<pre>".print_r($obj,true)."</pre>");

			// Draw Time
			$thaidrawtime = $this->get_thai_lottery_drawtime($thailotterydrawtime);
			$thaijsondraw = json_decode($thaidrawtime);
			$thailatestdraw = end($thaijsondraw);
			$day_month_draw = substr($thailatestdraw, 0, -4);
			$showdate = implode("-", str_split($day_month_draw, 2)). "-" . date("Y");
			$lastdrawdate = date("d F, Y", strtotime($showdate));

			$response .= '<h3>Latest Live Updates: ' . $lastdrawdate . '</h3>';

			// Lottery Table
			$response .= '<div class="lottery_table"><table>';
			$response .= '<tr>';
			$response .= '<th colspan="4">1st prize</th>';
			$response .= '<th colspan="2">3 page numbers</th>';
			$response .= '<th colspan="2">Last 3 digits</th>';
			$response .= '<th colspan="2">Last 2 digits</th>';
			$response .= '</tr><tr>';
			$response .= '<td colspan="4" class="lotonum1">' . $obj[0][1] . '</td>';
			$response .= '<td class="lotonum2">' . $obj[1][1] . '</td><td class="lotonum2">' . $obj[1][2] . '</td>';
			$response .= '<td class="lotonum2">' . $obj[2][1] . '</td><td class="lotonum2">' . $obj[2][2] . '</td>';
			$response .= '<td colspan="2" class="lotonum3">' . $obj[3][1] . '</td>';
			$response .= '</tr><tr>';
			$response .= '<th colspan="5">Side prize 1st prize</th>';
			$response .= '<th colspan="5">Side prize 2nd prize</th>';
			$response .= '</tr><tr>';
			$response .= '<td colspan="3">' . $obj[4][1] . '</td><td colspan="2">' . $obj[4][2] . '</td>';
			$response .= '<td>' . $obj[5][1] . '</td><td>' . $obj[5][2] . '</td><td>' . $obj[5][3] . '</td><td>' . $obj[5][4] . '</td><td>' . $obj[5][5] . '</td>	';
			$response .= '</tr><tr>';
			$response .= '<th colspan="10">Side prize 3rd prize</th>';
			$response .= '</tr><tr>';
			foreach (array_slice($obj[6],1) as $data) {
				$response .= '<td>' . $data . '</td>';
			}
			$response .= '</tr>';
			
			// 4th prize
			$response .= '<tr><th colspan="10">Side prize 4th prize</th></tr>';

			$count = 0;
			foreach (array_slice($obj[7],1) as $content) {
				++$count;
				if($count == 1) {  
					$response .= '<tr>';
				}
				$response .= '<td>';
				$response .= $content;
				$count;
				$response .= '</td>';
				if ($count == 10) {
					$response .= "</tr>";
					$count = 0;
				}
			}
			if ($count > 0) {
				$response .= "</td>";
			}
			$response .= '</tr>';

			// 5th prize
			$response .= '<tr><th colspan="10">Side prize 5th prize</th><tr>';
			foreach (array_slice($obj[8],1) as $content) {
				++$count;
				if($count == 1) {  
					$response .= '<tr>';
				}
				$response .= '<td>';
				$response .= $content;
				$count;
				$response .= '</td>';
				if ($count == 10) {
					$response .= "</tr>";
					$count = 0;
				}
			}
			if ($count > 0) {
				$response .= "</td>";
			}
			$response .= '</tr>';
			$response .= '</table></div>';

			$all_response .= $response;
			file_put_contents($cache_file, $all_response);
		} else {
			$all_response = file_get_contents($cache_file);
		}
		return $all_response;
	}

	private function get_thai_lottery_drawtime($thailotterydrawtime) {
		$drawyear = date('Y')+543;
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://thai-lottery1.p.rapidapi.com/gdpy?year=" . $drawyear,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"x-rapidapi-host: thai-lottery1.p.rapidapi.com",
				"x-rapidapi-key: b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}

	private function get_thai_lottery($thailottery) {
		$drawtime = $this->get_thai_lottery_drawtime($thailotterydrawtime);
		$jdraw = json_decode($drawtime);
		$latestdraw = end($jdraw);

		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://thai-lottery1.p.rapidapi.com/?date=" . $latestdraw,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"x-rapidapi-host: thai-lottery1.p.rapidapi.com",
				"x-rapidapi-key: b6f33c4ba7msh735c1886037388cp1e65cejsn86201e3a7c05"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return $response;
		}
	}
}