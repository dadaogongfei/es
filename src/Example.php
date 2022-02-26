<?php
class Example
{
    public function setSettings(): Example
    {
        $esClient = app('es');
        $settings = [
            'number_of_shards'=>3,
            'number_of_replicas'=>0,
            'refresh_interval'=>'1s'
        ];
        try {
            $esClient->setIndex('news')->setSettings($settings);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        return $this;
    }
    public function buildMappings()
    {
        $esClient = app('es');
        $mappings = [
            'title'=>[
                'type' => 'text',
                'analyzer'=>'ik_max_word',
                'search_analyzer'=>'ik_smart'
            ],
            'digest'=>[
                'type' => 'text',
                'analyzer'=>'ik_max_word',
                'search_analyzer'=>'ik_smart'
            ],
            'count'=>[
                'type'=>'integer'
            ],
            'source'=>[
                'type'=>'keyword'
            ],
            'type'=>[
                'type' => 'integer',
                'fields'=>[
                    'keywords'=>[
                        'type'=>'keyword'
                    ]
                ]
            ],
            'date'=>[
                'type'=>'date',
                "format"=>"yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis"
            ]
        ];
         $esClient->setIndex('zyw')->buildMappings($mappings);
    }
    public function deleteIndex()
    {
        $esClient = app('es');
        $esClient->setIndex('zyw')->deleteIndex();
    }
    public function checkIndexIsExits()
    {
         $esClient = app('es');
         $esClient->setIndex('zyw')->checkIndexIsExits();
    }
    public function getSettings()
    {
        $esClient = app('es');
        $ret = $esClient->setIndex('zyw')->getSettings(["food",'coins']);
    }
    public function getMappings()
    {
        $esClient = app('es');
        $ret = $esClient->setIndex('zyw')->getMappings(["food",'coins']);
    }
    public function buildIndex()
    {
        $settings=[
            "number_of_shards"=>3,
            "number_of_replicas"=>0,
            "refresh_interval"=>'1s'
        ];
        $esClient = app('es');
        $esClient->setIndex('news')->buildIndex($settings);
    }
    public function add()
    {
        $esClient = app('es');
        $data['title'] = "生活有点难：代驾小哥被妻子嫌穷街头痛哭，干部体验送外卖累瘫";
        $data['source'] = "2021年不知不觉已经到了尾声，回首过去这一年，生活是不是有";
        $data['type'] = 4;
        $data['count'] = 10;
        $data['date'] = "2021-12-06 07:53:09";
        $esClient->setIndex("news")->add($data);
    }
    public function editIndex()
    {
        $esClient = app('es');
        $data['type'] = 3;
        $esClient->setIndex("news")->editIndex("L7l8bH4B0eHa1xBbpoaA",$data);
    }
    public function getDocumentById()
    {
        $esClient = app('es');
        $data = $esClient->setIndex("news")->getDocumentById("L7l8bH4B0eHa1xBbpoaA");
    }
    public function search()
    {
        //简单查询
        $search['match']['title'] = "男子";
        //复合查询
        $search['bool']['must']['match']['title'] = "男子";
        $search['bool']['must_not']['match']['digest'] = "";
        $search['bool']['should']['match']['title'] = "女人";
        $search['bool']['filter']['range']['count']['gt'] =28582;
        // match_all 查询
        $search['match_all'] = new \stdClass();
        //multi_match 查询
        $search['multi_match']['query'] = "女人";
        $search['multi_match']['fields'] = ['title','digest'];
        //range 查询
        $search['range']['count']['gte'] = 100;
        $search['range']['count']['lt']  = 10000;
        $esClient = app('es');
        //term 查询
        $search['term']['count'] =5;
        //constant_score 查询
        $search['constant_score']['filter']['term']['count'] =5;

        $ret = $esClient->setIndex('news')->buildQuery($search)->search();
    }
}