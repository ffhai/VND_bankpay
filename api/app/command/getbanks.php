<?php
declare (strict_types = 1);

namespace app\command;

use app\common\model\BanksModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class getbanks extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('getbanks')
            ->setDescription('the getbanks command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 指令输出
        $output->writeln('getbanks');
        $vietqr = new \Vietqr();
        $req = $vietqr->banks();
        $obj = json_decode($req,true);
        if($obj['code'] != '00'){
            $output->writeln($req);
            return;
        }
        $output->writeln($obj['desc']);

        $banksModel = new BanksModel();
        foreach($obj['data'] as $item){
            /*if(!$item['transferSupported']){
                continue;
            }*/
            if(empty($item['swift_code'])){
                continue;
            }
            $data = [
                'name'=>$item['name'],
                'code'=>$item['code'],
                'bin'=>$item['bin'],
                'logo'=>$item['logo'],
                'short_name'=>$item['short_name'],
                'swift_code'=>$item['swift_code'],
            ];
            $req = $banksModel->where('bin','=',$data['bin'])->field('id')->find();
            if(!$req){
                $banksModel->insert($data);
                $output->writeln('add->'.$data['code']);
            }else{
                $output->writeln('isset->'.$data['code']);
            }

        }

    }
}
