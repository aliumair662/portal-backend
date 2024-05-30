<?php

namespace App\Models\Odoo;

use Edujugon\Laradoo\Odoo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class Base
{
    private $odoo = null;

    public function __construct()
    {
    }

    public function connect()
    {
        if (!app()->bound('odoo')) {
            app()->singleton('odoo', function ($app) {
                return (new Odoo())
                    ->username(config('odoo.username'))
                    ->password(config('odoo.password'))
                    ->db(config('odoo.db'))
                    ->host(config('odoo.host'))
                    ->connect();
            });
        } else {
        }
        $this->odoo = app('odoo');
        return $this->odoo;
    }

    public function getMany()
    {
        $pieces = array_chunk($this->fields, ceil(count($this->fields) / 3));

        $result = collect([]);
        foreach ($pieces as $piece) {
            $piece[] = 'id';
            $piece = array_unique($piece);

            $res = $this->connect()->fields($piece)->get($this->resource);
            $result = $result->merge($res)
                ->groupBy('id')
                ->map(function ($items) {
                    return Arr::collapse($items);
                });
        }
        return $result->values();
    }

    public function get($cache = false)
    {

        if (!isset($this->resource)) {
            return;
        }

        if (Redis::exists($this->resource) && $cache == false) {
            return collect(json_decode(Redis::get($this->resource), true));
        } else {

            if (isset($this->fields)) {

                if (count($this->fields) > 10) {
                    $res = $this->getMany();
                } else {
                    $res = $this->connect()->fields($this->fields)->get($this->resource);
                }
            } else {
                $res = $this->connect()->get($this->resource);
            }

            Redis::set($this->resource, $res);
            return $res;
        }
    }

    public function children()
    {
        $this->data->transform(function ($item) {
            if ($item['child_ids']) {
                foreach ($item['child_ids'] as $child_id) {
                    $item['children'][] = $this->connect()->where('id', '=', $child_id)->fields($this->fields)->get($this->resource)->first();
                }
            }
            return $item;
        });
    }

    public function getById($id, $clearCache = false)
    {
        $this->data = $this->connect()->where('id', '=', $id)->fields($this->fields)->get($this->resource);
        $this->children();
        return $this;
    }

    public function cache($key, \Closure $data, $clearCache = false, $expiryTime = 0)
    {
        if ($clearCache == true) {
            Redis::del($key);
        }

        // Log::debug('Loading: ' .$key);
        if (Redis::exists($key)) {
            // Log::debug('found: ' . $key);
            return collect(json_decode(Redis::get($key), true));
        }

        if ($expiryTime > 0) {
            Redis::setex($key, $expiryTime, $data());
        } else {
            Redis::set($key, $data());
        }
        // Log::debug('new cache: ' . $key);
        return collect(json_decode(Redis::get($key), true));
    }

    public function cacheRemember($key,\Closure $closure){
       return Cache::remember($key, 1800, $closure);
    }

    public function update($id, $data)
    {
        return $this->connect()->where('id', '=', $id)->update($this->resource, $data);
    }

    public function create()
    {
        return $this->connect()->create($this->resource, [
            $this->data
        ]);
    }
}
