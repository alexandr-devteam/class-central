<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 8/15/14
 * Time: 5:02 PM
 */

namespace ClassCentral\SiteBundle\Utility;


use Symfony\Component\HttpFoundation\Response;

class UniversalHelper {

    /**
     * Standard format for ajax api call response
     * @param bool $success
     * @param string $message
     */
    public  static function getAjaxResponse($success = false, $message = '')
    {
        $response = array(
            'success' => $success,
            'message' => $message
        );

        return new Response(json_encode($response));
    }

    public static function  commaSeparateList( $items = array() )
    {
        $itemCount = count($items);
        $str = '';
        if($itemCount > 1)
        {
            $str = implode(', ' , array_slice($items,0,$itemCount-1)) . ' and ' . end($items);
        }
        else
        {
            $str = implode(', ' , $items);
        }

        return $str;
    }

    public static function getSlug( $str )
    {
        $url = preg_replace('~[^\\pL0-9_]+~u', '-',$str);
        $url = trim($url, "-");
        $url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
        $url = strtolower($url);
        $url = preg_replace('~[^-a-z0-9_]+~', '', $url);

        return $url;
    }

} 