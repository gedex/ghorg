<?php

namespace Gedex\Ghorg\Command;

use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command {
    /**
     * @var string
     */
    protected $membersFilterApi = 'all';

    /**
     * @var string
     */
    protected $reposTypeApi = 'all';

    /**
     * Get service from application's container.
     *
     * @param  string $serviceName Service's name
     * @return mixed               Service
     */
    protected function get($serviceName)
    {
        return $this->getApplication()->getContainer()->get($serviceName);
    }

    /**
     * Parse filter string.
     *
     * @param  string $filter Filter string
     * @return array          Parsed filter
     *
     * @throws \Exception
     */
    protected function parseFilterString($filter)
    {
        parse_str($filter, $parsedFilter);

        $readyFilter = array();
        foreach ($parsedFilter as $field => $value) {
            // Specialized filter to be passed to API client.
            if ('2fa_disabled' === $field) {
                $this->membersFilterApi = '2fa_disabled';
                continue;
            }
            if ('type' === $field && in_array($value, array('all', 'public', 'private', 'forks', 'sources', 'member'))) {
                $this->reposTypeApi = $value;
                continue;
            }

            // By default if value is string then '===' comparison will be
            // used. If array, key 'operator' and key 'value' MUST exist.
            if (is_array($value)) {
                if (empty($value['operator']) || empty($value['value'])) {
                    throw new \Exception('Invalid filter');
                }

                if (!in_array($value['operator'], \Arrch\Arrch::$operators)) {
                    throw new \Exception(sprintf('Invalid operator %s for field %s', $value['operator'], $field));
                }

                $value['type'] = $value['type']?: 'string';
                switch ($value['type']) {
                    case 'int':
                    case 'integer':
                    case 'number':
                        $value['value'] = (int) $value['value'];
                        break;
                    case 'string':
                    default:
                        $value['value'] = (string) $value['value'];
                        break;
                }

                $readyFilter[] = array($field, $value['operator'], $value['value']);
            } else {
                $readyFilter[] = array($field, (string) $value);
            }
        }

        return $readyFilter;
    }

    /**
     * Flatten multidimensional array in which nested array will be prefixed with
     * parent keys separated with dot char, e.g. given an array:
     *
     *     array(
     *         'a' => array(
     *             'b' => array(
     *                 'c' => ...
     *             )
     *         )
     *     )
     *
     * a flatten array would contain key 'a.b.c' => ...
     *
     * @param array  $arr    Array that may contains nested array
     * @param string $prefix Prefix
     *
     * @return array Flattened array
     */
    protected function flatten_array( $arr, $prefix = '' ) {
        $flattened = array();
        foreach ( $arr as $key => $value ) {
            if ( is_array( $value ) ) {
                if ( sizeof( $value ) > 0 ) {
                    $flattened = array_merge( $flattened, $this->flatten_array( $value, $prefix . $key . '.' ) );
                } else {
                    $flattened[ $prefix . $key ] = '';
                }
            } else {
                $flattened[ $prefix . $key ] = $value;
            }
        }
        return $flattened;
    }

    /**
     * Unflatten array will make key 'a.b.c' becomes nested array:
     *
     *     array(
     *         'a' => array(
     *             'b' => array(
     *                 'c' => ...
     *             )
     *         )
     *     )
     *
     * @param  array $arr Flattened array
     * @return array
     */
    protected function unflatten_array( $arr ) {
        $unflatten = array();
        foreach ( $arr as $key => $value ) {
            $key_list  = explode( '.', $key );
            $first_key = array_shift( $key_list );
            $first_key = $this->get_normalized_array_key( $first_key );
            if ( sizeof( $key_list ) > 0 ) {
                $remaining_keys = implode( '.', $key_list );
                $subarray       = $this->unflatten_array( array( $remaining_keys => $value ) );
                foreach ( $subarray as $sub_key => $sub_value ) {
                    $sub_key = $this->get_normalized_array_key( $sub_key );
                    if ( ! empty( $unflatten[ $first_key ][ $sub_key ] ) ) {
                        $unflatten[ $first_key ][ $sub_key ] = array_merge( $unflatten[ $first_key ][ $sub_key ], $sub_value );
                    } else {
                        $unflatten[ $first_key ][ $sub_key ] = $sub_value;
                    }
                }
            } else {
                $unflatten[ $first_key ] = $value;
            }
        }
        return $unflatten;
    }
}
