<?php

namespace Og\OptimaClass\Components;

use Cocur\Slugify\Slugify;
use Og\OptimaClass\Helpers\Cms;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class Urlhelper
{
    public static function slug($tag)
    {
        return Cms::getSlugByTagName($tag);
    }

    public static function propertiesListingUrl()
    {
        return URL::to(self::slug('propertyList'));
    }

    public static function propertyDetailsSlug()
    {
        return self::slug('propertyDetails');
    }

    public static function developmentsListingUrl()
    {
        return URL::to(self::slug('developmentsListing'));
    }

    public static function developmentDetailsSlug()
    {
        return self::slug('developmentDetails');
    }

    public static function blogListingUrl()
    {
        return URL::to(self::slug('blogListing'));
    }

    public static function blogDetailsSlug()
    {
        return self::slug('blogDetails');   
    }

    /**
     * Urlhelper::getPropertyTitle($property, $language)
     * 
     * @param mixed $property
     * @param mixed $language
     * 
     * @return string
    */
    public static function getPropertyTitle($property, $language = '')
    {
        $lang = empty($language) ? strtoupper(App::getLocale()) : strtoupper($language);
        $slugify = new Slugify();
        $permaLink = isset($property['slug_all'][$lang]) ? $property['slug_all'][$lang] : 'property';

        return $slugify->slugify($permaLink) . '_' . $property['id'];
    }

    /**
     * Urlhelper::getPropertyUrl($property)
     * 
     * @param mixed $property
     * 
     * @return url
     */
    public static function getPropertyUrl($property, $lang = "en")
    {
        return URL::to($lang . '/' . self::propertyDetailsSlug() . '/' . self::getPropertyTitle($property,App::getlocale()));
    }

    /**
     * Urlhelper::getCommercialPropertyUrl($property)
     * 
     * @param mixed $property
     * 
     * @return url
     */
    public static function getCommercialPropertyUrl($property, $language)
    {
        $sale_slug = Urlhelper::slug('Sale Details');
        $holiday_slug = Cms::getSlugByTagName('Rent Details');
        $rent_slug = Cms::getSlugByTagName('Holiday Details');
        $development_slug = Cms::getSlugByTagName('New Development Details');
        $auction_slug = Urlhelper::slug('auction_tag');

        $params = App::bound('params') ? App::make('params') : [];        
        $slug = isset($params->slug) && !empty($params->slug) ? $params->slug : "";
        
        $urlProperty = '';
        if (in_array($slug, array($rent_slug, $holiday_slug))) {
            if (isset($property['urls']['rent_url']) && !empty($property['urls']['rent_url'])) {
                $urlProperty = isset($property["urls"]["rent_url"][$language]) ? $property["urls"]["rent_url"][$language] : '';
                if($slug == $holiday_slug){
                    $urlProperty = str_replace($rent_slug, $holiday_slug, $urlProperty);
                }
            }
        } else if ($slug == $auction_slug) {
            if (isset($property['urls']) && !empty($property['urls'])) {
                $urlProperty = isset($property['urls']['sale_url'][$language]) ? $property['urls']['sale_url'][$language] : '';
                $urlProperty = str_replace($sale_slug, $auction_slug, $urlProperty);
            }
        } else {            
            if (isset($property['urls']['sale_url']) && !empty($property['urls']['sale_url'])) {
                $urlProperty = isset($property['urls']['sale_url'][$language]) ? $property['urls']['sale_url'][$language] : '';
                if($slug == $development_slug){
                    $urlProperty = str_replace($sale_slug, $development_slug, $urlProperty);
                }
            }
        }
        return $urlProperty;
    }

    /**
     * Urlhelper::getDevelopmentTitle($development, $language)
     * 
     * @param mixed $development
     * @param mixed $language
     * 
     * @return string
     */
    public static function getDevelopmentTitle($development, $language = '')
    {
        $lang = empty($language) ? strtoupper(App::getLocale()) : strtoupper($language);
        $slugify = new Slugify();
        $permaLink = isset($development['slug_all'][$lang]) ? $development['slug_all'][$lang] : 'property';

        return $slugify->slugify($permaLink) . '_' . $development['id'];
    }

    /**
     * Urlhelper::getDevelopmentUrl($development, $language)
     * 
     * @param mixed $development
     * 
     * @return url
     */
    public static function getDevelopmentUrl($development, $lang = "en")
    {
        return URL::to($lang . '/' . self::developmentDetailsSlug() . '/' . self::getDevelopmentTitle($development));
    }

    /**
     * Urlhelper::getPostTitle($post, $language)
     * 
     * @param mixed $post
     * @param mixed $language
     * 
     * @return string
     */
    public static function getPostTitle($post, $language = '')
    {
        $lang = empty($language) ? strtoupper(App::getLocale()) : strtoupper($language);
        $slugify = new Slugify();
        $postSlug = isset($post['slug_all'][$lang]) ? $post['slug_all'][$lang] : 'post-not-found';

        return $slugify->slugify($postSlug);
    }

    /**
     * Urlhelper::getBlogUrl($development, $language)
     * 
     * @param mixed $post
     * @param string $language
     * 
     * @return url
     */
    public static function getBlogUrl($post, $lang = "en")
    {
        return URL::to($lang . '/' . self::blogDetailsSlug() . '/' . self::getPostTitle($post));
    }

    /**
     * get_languages_dropdown()
     *
     * @return void
     */
    public static function get_languages_dropdown($object)
    {
        $property = $object->has('property') ? $object->get('property') : [];
        $development = $object->has('development') ? $object->get('development') : [];
        $post = $object->has('post') ? $object->get('post') : [];
        $page_data = $object->has('page_data') ? $object->get('page_data') : [];
        $languages = Sitehelper::get_languages();
        $cmsModels = Cms::Slugs('page');

        foreach ($languages as $language) {
            $slug = isset($page_data['slug_all'][$language['key']]) ? $page_data['slug_all'][$language['key']] : (isset($page_data['slug_all']['EN']) && $page_data['slug_all']['EN'] != '' ? $page_data['slug_all']['EN'] : '');

            if ($development) {
                $title = self::getDevelopmentTitle($development, $language['key']);
                $slug .= '/' . $title;
            } elseif ($post) {
                $title = self::getPostTitle($post, $language['key']);
                $slug .= '/' . $title;
            }

            if (isset($page_data['slug_all']['EN']) && ($page_data['slug_all']['EN'] == 'home' || $page_data['slug_all']['EN'] == 'index')) {
                $url = ['language' => strtolower($language['key'])];
            } else {
                $url = ['language' => strtolower($language['key']), 'slug' => $slug];
            }

            $get_params = request()->query() ? request()->query() : [];

            if (isset($cmsModels) && !empty($cmsModels)) {
                foreach ($cmsModels as $model) {
                    if (isset($model['type']) && ($model['type'] == 'LocationsGroup' || $model['type'] == 'PropertyTypes') && $slug == $model['slug']) {
                        $get_params = [];
                    }
                }
            }

            unset($get_params['params']['st_rental'], $get_params['params']['pagename']);

            $url_to = self::buildUrl(array_merge($url, ['params' => $get_params]));

            if ($property) {
                $url_to = self::getPropertyUrl($property, strtolower($language['key']));
            }

            if (strtolower($language['key']) != strtolower(App::getLocale())) {
                echo '<li><a class="dropdown-item" href="' . URL::to($url_to) . '">' . $language['title'] . '</a></li>';
            }
        }
    }

    public static function parseUrl($url)
    {
        // Parse the URL
        $parsedUrl = parse_url($url);

        // Explode the path by '/' and remove any empty values
        $pathSegments = array_filter(explode('/', $parsedUrl['path']));

        // Get language, slug, and title
        $language = $pathSegments[1] ?? null;
        $slug = $pathSegments[2] ?? null;
        $title = $pathSegments[3] ?? null;

        // Get query parameters
        parse_str($parsedUrl['query'] ?? '', $params);

        $result = [];

        // If language is set, include it
        if ($language) {
            $result['language'] = $language;
        }

        // If slug is set, include it
        if ($slug) {
            $result['slug'] = $slug;
        }

        // If title is set, include it
        if ($title) {
            $result['title'] = $title;
        }

        // If query parameters are set, include them
        if (!empty($params)) {
            $result['params'] = $params;
        }

        return $result;
    }

    public static function buildUrl(array $data)
    {
        // Start with the base URL
        $url = url('/');

        // Append the language
        if (isset($data['language'])) {
            $url .= '/' . $data['language'];
        }

        // Append the slug
        if (isset($data['slug'])) {
            $url .= '/' . $data['slug'];
        }

        // Append the title (if available)
        if (isset($data['title'])) {
            $url .= '/' . $data['title'];
        }

        // Append query parameters (if any)
        if (isset($data['params']) && !empty($data['params'])) {
            $url .= '?' . http_build_query($data['params']);
        }

        return $url;
    }
}