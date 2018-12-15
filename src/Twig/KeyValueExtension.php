<?php
/**
 * Created by PhpStorm.
 * User: Antoine
 * Date: 29/01/2017
 * Time: 12:32.
 */

namespace Mgate\DashboardBundle\Extension;

use Webmozart\KeyValueStore\Api\KeyValueStore;

class KeyValueExtension extends \Twig_Extension
{
    protected $keyValueStore;

    public function __construct(KeyValueStore $keyValueStore)
    {
        $this->keyValueStore = $keyValueStore;
    }

    public function getName()
    {
        return 'mgate_dashboardbundle_json_key_value_store_extension';
    }

    public function getFunctions()
    {
        return [
            'param' => new \Twig_Function_Method($this, 'param'),
        ];
    }

    public function param($name)
    {
        if (!$this->keyValueStore->exists($name)) {
            return 'undef key ' . $name;
        }

        $value = $this->keyValueStore->get($name);

        return !empty($value) ? $value : null;
    }
}
