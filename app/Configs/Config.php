<?php

namespace App\Configs;


abstract class Config
{
    abstract protected function getName():string;
    abstract protected function fieldNames():array;

    public function __construct()
    {
        $config = \App\Config::firstOrCreate(['name' => $this->getName()],
            ['settings' => json_encode((array)$this)
            ]);

        $settings = json_decode($config->settings, true);

        $this->apply($settings);
    }

    public function fieldName($name){
        $names  = $this->fieldNames();
        if(isset($names[$name])){
            return $names[$name];
        }else{
            return $name;
        }
    }

    public function apply(array $fields){
        foreach ($this as $key => $_) {
            if (isset($fields[$key])) {
                $this->$key = $fields[$key];
            }
        }
    }

    public function save():bool{
        $config = \App\Config::where('name' , $this->getName())->first();
        $config->settings = json_encode((array)$this);
        return $config->save();
    }

}
