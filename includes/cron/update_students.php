<?php

// namespace ##
namespace Q_Meet_Our_Students\Cron;

use Q_Meet_Our_Students\Core\Helper as Helper;

/**
 * Grab student list from GO web service
 *
 * @package   Q_Meet_Our_Students
 */
class Update_Students {

    protected $post_id = null; // ID of current student profile being updated ##

    /**
     * Construct
     *
     * @since       0.2
     * @return      void
     */
    public function __construct()
    {

        // force delete the saved student data ##
        #\delete_site_option( 'q_mos_students' );

        // hook our "soap" method to the added cron schedule 'q_mos_update_students' ##
        add_action( 'q_mos_update_students', array( $this, 'update' ) );

        // run manually ##
        #add_action( 'init', array( $this, 'update' ), 5 );

    }


    /**
     * Hook into our added cron schedule point "q_mos_update_students"
     *
     * @since 0.2
     * @return void
     */
    public function update()
    {

        Helper::log( 'Cron Running: Update Student...' );

        if ( ! $students = \get_site_option( 'q_mos_students', false , false ) ) {

            return false;

        }

        // unserliaze student list ##
        $students = \maybe_unserialize( $students );

        #Helper::pr( $students ); wp_die();

        // we need an array - so check ##
        if ( ! is_array( $students ) ) {

            return false;

        }

        // check and rebuild list of countries ##
        $this->set_countries();

        // check and rebuild list of genders ##
        $this->set_genders();

        // get all students currently stored in WordPress ##
        $current_students = $this->get_current_students();
        #Helper::pr( $current_students ); wp_die();

        // loop over each student, check for matching record and update ##
        foreach ( $students as $key => $value ) {

            // cast to object ##
            $value = (object) $value;

            // check for a matching post - using post_title ( post_name might get incremented )
            if ( $post = get_page_by_title( $value->Name, OBJECT, 'student' ) ) {

                // update tracking ID - used in later queries ##
                $this->post_id = $post->ID;

                // delete from $current_students list - so we don't delete this profile at the end of the process ##
                if ( ( $key = array_search( $this->post_id, $current_students ) ) !== false ) {

                    unset( $current_students[$key] );

                }

            }

            // insert / update post ##
            $post_args = array(
                'ID'            => $this->post_id, // if found in get_page_by_title - ID is set ( update ), if not - null ( insert ) ##
                'post_title'    => \sanitize_text_field( $value->Name ),
                'post_content'  => isset( $value->Biography ) ? \sanitize_text_field( $value->Biography ) : false,
                #'post_excerpt'  => '',
                'post_status'   => 'publish',
                'post_author'   => 1, // default to main author ##
                'post_type'     => 'student',
                'comment_status'=> 'closed',
                'ping_status'   => 'closed'
            );

            // Insert the post into the database ##
            // also updates the value of $this->post_id if insert completes ##
            if ( ! $this->post_id = \wp_insert_post( $post_args ) ) {

                Helper::log( 'Failed to save student: '.$this->post_id );

                continue;

            }


            // add post_meta ##
            \add_post_meta( $this->post_id, 'mos_age', \sanitize_key( $value->Age ), true );
            \add_post_meta( $this->post_id, 'mos_unique_id', \sanitize_key( $value->ID ), true );
            \add_post_meta( $this->post_id, 'mos_program_start_date', \sanitize_text_field( $value->ProgramStart ), true );


            // add taxonomy data ##
            $countries = $this->get_countries(); // fakes for now, as data not random enough ##
            $random_country = $countries[ array_rand(  $countries) ];
            $this->set_taxonomy( 'mos_country', $random_country ); // $value->Nationality

            $this->set_taxonomy( 'mos_gender', $value->Gender );

            // interests need a bit of extra work - for now, as provided data is corrupt ##
            $this->set_taxonomy( 'mos_interest', $this->format_interests( $value->Interests ) );

            // rest $this->post_id
            $this->post_id = null;

        }

        // delete all old students profiles ##
        $this->delete_posts( $current_students );

        // delete stored student data ##
        \delete_site_option( 'q_mos_students' );

        // log that we're done ##
        Helper::log( 'Cron finished: Update Students...' );

    }


    protected function delete_posts( $data = null )
    {

        if ( is_null ( $data ) || ! is_array( $data ) ) {

            return false;

        }

        foreach( $data as $post_id ) {

            Helper::log( 'Delete Post: '.$post_id );

            wp_delete_post( $post_id, true );

        }

    }


    protected function format_interests( $data = null )
    {

        if ( is_null ( $data ) ) {

            return false;

        }

        // split string into an array ##
        // "Exercise/Sports, Reading/Writing1" ##
        $interests = explode( ",", $data );

        $array = array();

        foreach( $interests as $interest ) {

            // remove numbers ##
            $value = preg_replace( "/\d+$/","", $interest );

            $array[] = $value;

        }

        return $array;

    }



    /**
     * Set object terms
     *
     * @param  $taxonomy slug
     * @return boolean
     */
    protected function set_taxonomy( $taxonomy = null, $terms = null, $limit = null )
    {

        // sanity check ##
        if (
            is_null( $this->post_id )
            || is_null( $taxonomy )
            || is_null( $terms )
        ) {

            return false;

        }

        // sanitize terms ##
        $terms =
            is_array( $terms ) ?
            array_map( '\sanitize_text_field', \wp_unslash( $terms ) ) :
            \sanitize_text_field( $terms ) ;

        // are we limiting the number of terms allowed ? ##
        if ( ! is_null( $limit ) && is_array( $terms ) ) {

            array_splice( $terms, $limit );

        }

        // set object terms
        $do = \wp_set_object_terms( $this->post_id, $terms, $taxonomy );

        // compatibility ##
        if ( \is_wp_error( $do ) ) {

            Helper::log( 'Failed to save terms for: '.$taxonomy.' / '.$do->get_error_message() );

            // bad ##
            return false;

        }

        // good ##
        return true;

    }


    protected function get_current_students()
    {

        // get all students currently stored in WordPress ##
        $current_students = new \WP_Query(
            array(
                'posts_per_page'    => -1,
                'post_type'         => 'student',
                'fields'            => 'ids'
            )
        );

        /* Restore original Post Data */
        wp_reset_postdata();

        // test ##
        #Helper::pr( $students_delete );

        // check if we found any current students, if not start a new empty array ##
        return $current_students->posts && is_array( $current_students->posts ) ? $current_students->posts : array() ;

    }


    protected function match_terms( $objects = null, $key = null, $value = null )
    {

        #Helper::pr( 'Checking for: '.$value );

        if ( is_null( $objects ) || is_null( $key ) || is_null( $value ) ) {

            #Helper::pr( 'Kicked' );

            return false;

        }

        // $countries is an array of objects
        foreach ( $objects as $object ) {

            $objVars = get_object_vars($object);

            #Helper::pr( $objVars[$key] );
            #wp_die();

            if ( isset( $objVars[$key] ) && $objVars[$key] == $value ) {

                #Helper::pr( 'Found: '.$value );

                return true;

            }

        }

    }


    protected function set_genders()
    {

        // start blank ##
        $genders = false;

        #Helper::pr( \get_taxonomies() );

        // get current genders ##
        $genders = \get_terms( 'mos_gender', array(
            #'orderby'    => 'count',
            'hide_empty' => 0,
        ) );

        #Helper::pr( $genders );

        if ( empty( $genders ) || \is_wp_error( $genders ) ) {

            $genders = false;

        }

        #Helper::pr( $countries );

        // loop over all list of approved countries ##
        foreach ( $this->get_genders() as $key => $value ) {

            // check if this term exists ##
            if (
                $genders
                && ! $this->match_terms( $genders, 'name', $value )
            ) {

                Helper::pr( 'Adding Term: '.$value );

                // add term ##
                \wp_insert_term( $value, 'mos_gender', array( 'slug' => strtolower( $key ) ) );

            }

        }

    }

    protected function get_genders()
    {

        return array (
            'male'      => 'Male',
            'female'    => 'Female'
        );

    }


    protected function set_countries()
    {

        // start blank ##
        $countries = false;

        #Helper::pr( \get_taxonomies() );

        // get current countries ##
        $countries = \get_terms( 'mos_country', array(
            #'orderby'    => 'count',
            'hide_empty' => 0,
        ) );

        #Helper::pr( $countries );

        if ( empty( $countries ) || \is_wp_error( $countries ) ) {

            $countries = false;

        }

        #Helper::pr( $countries );

        // loop over all list of approved countries ##
        foreach ( $this->get_countries() as $key => $value ) {

            #Helper::pr( 'Checking for: '.$value );

            // check if this term exists ##
            if (
                $countries
                && ! $this->match_terms( $countries, 'name', $value )
            ) {

                #Helper::pr( 'Adding Term: '.$value );

                // add term ##
                \wp_insert_term( $value, 'mos_country', array( 'slug' => strtolower( $key ) ) );

            }

        }

    }


    protected function get_countries()
    {

        return array (
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua And Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia And Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo, Democratic Republic',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Cote D\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island & Mcdonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran, Islamic Republic Of',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle Of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KR' => 'Korea',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States Of',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts And Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre And Miquelon',
            'VC' => 'Saint Vincent And Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome And Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia And Sandwich Isl.',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard And Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad And Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks And Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Viet Nam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis And Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );

    }


}

