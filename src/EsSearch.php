<?php
namespace Zyw\Es;
use Elasticsearch\ClientBuilder;
use phpDocumentor\Reflection\Types\Mixed_;

class EsSearch
{
    public $client;
    public $index;
    public $condition = [];
    public $from;
    public $size;
    public $sort;
    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(config('es.host'))
            ->build();
    }
    public function setIndex($index): EsSearch
    {
        $this->index = $index;
        return $this;
    }
    public function buildIndex($settings=[]): EsSearch
    {
        $settings = array_merge([
            'number_of_shards' => 2,
            'number_of_replicas' => 0
        ],$settings);
        $params = [
            'index' => $this->index,
            'body'  => [
                'settings' =>$settings
            ]
        ];
        try {
            $response = $this->client->indices()->create($params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $this;
    }

    /**
     *
     * @param array $properties
     */

    public function buildMappings($properties=[]): bool
    {
        $params = [
            'index' => $this->index,
            'body' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' =>$properties
                ]
        ];
        try {
            $response = $this->client->indices()->putMapping($params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
       return $response ?  ($response['acknowledged'] ?? false) :false;
    }
    public function add($data=[]): bool
    {
        if (empty($data)) {
            throw new \Exception("data is empty");
        }
        $params = [
            'index' => $this->index,
            'body' => $data
        ];
        try {
            $response = $this->client->index($params);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $response ? $response['result']=="created" :false;
    }

    /**
     * 分更新文档(如更改现存字段，或添加新字段)
     * @param $id
     * @param array $data
     */
    public function editIndex($id,$data=[]): bool
    {
        $params = [
            'index' => $this->index,
            'id' =>$id,
            'body' => [
                'doc' => $data
            ]
        ];
        try {
            $response = $this->client->update($params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $response ? $response['successful']==1 :false;
    }

    /**
     * @return false|mixed
     */
    public function deleteIndex(): bool
    {
        $deleteParams = [
            'index' => $this->index
        ];
        try {
            $response =  $this->client->indices()->delete($deleteParams);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $response ?  ($response['acknowledged'] ?? false) :false;
    }
    public function deleteDocument($id): bool
    {
        $params = [
            'index' => $this->index,
            'id'    => $id
        ];
        try {
            $response = $this->client->delete($params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $response ? $response['successful']==1:false;
    }
    public function checkIndexIsExits($index): bool
    {
        $index = empty($index) ? $this->index:$index;
        $params = ['index' =>$index];
        return $this->client->indices()->exists($params);
    }
    public function setSettings($settings=[]): bool
    {
        $settings = array_merge([
            'number_of_replicas' => 0,
            'refresh_interval' => -1
        ],$settings);
        $params = [
            'index' => $this->index,
            'body' => [
                'settings' =>$settings
            ]
        ];
        $response = $this->client->indices()->putSettings($params);
        return $response ?  ($response['acknowledged'] ?? false) :false;
    }
    public function getSettings($indexs=[])
    {
        $indexs = empty($indexs) ? [$this->index]:$indexs;
        foreach ($indexs as $index) {
            $ret = $this->checkIndexIsExits($index);
            if ($ret===false) {
                throw new \Exception("{$index} not exits");
            }
        }

        $params = ['index' => $indexs];
        return $this->client->indices()->getSettings($params);
    }
    public function getMappings($index=[])
    {
         $index = empty($index) ? $this->index:$index;
         $params = ['index' => $index];
         return $this->client->indices()->getMapping($params);
    }

    /**
     * @param $id
     * @return false|mixed
     * @throws \Exception
     */
    public function getDocumentById($id) :mixed
    {
        $params = [
            'index' =>$this->index,
            'id' => $id
        ];
        try {
            $response = $this->client->get($params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $response ? $response['found'] ? $response['_source']:false : false;
    }
    public function buildQuery($condition): EsSearch
    {
          $this->condition = $condition;
          return $this;
    }

    public function sort($field,$order): EsSearch
    {
        $this->sort =["$field"=>"$order"];
        return $this;
    }
    public function search($from=0,$limit=20)
    {
        $this->from = $from;
        $this->size = $limit;
        $params = [
            'index' => $this->index,
            'body' => [
                'query' =>$this->condition,
                'from'=>$from,
                'size'=>$limit,
            ]
        ];
        if (!empty($this->sort)) {
            $params['body']['sort'] = $this->sort;
        }
        $results = $this->client->search($params);
        return['total'=>$results['hits']['total']['value'],'list'=>array_column($results['hits']['hits'],'_source')];
    }
    public function isHealthy()
    {
        $info = $this->client->cluster()->health();

        return $info['status'];
    }
}