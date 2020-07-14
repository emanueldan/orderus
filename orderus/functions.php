<?

class XGame {
    private static
    $initialized = false,
    $gameHealth,
    $gameCount = 0,
    $runs = 20,
    $lang = array();
    
    public static function run(){
        include 'lang/en.php';
        self::initGame();
    }
    private static function lang($key){
        return self::$lang[$key];
    }
    private static function initGame(){
        $game = array();
        while(self::$gameCount <= self::$runs) {
            $game[self::$gameCount] = self::game();
        }
        self::gameTemplate($game);
    }
    private static function game(){
        $game =array();
        self::$gameCount++;
        if(self::$gameCount <= self::$runs){
            $roundStats = self::attack();
            if($roundStats){
                $game['rounds'][] = $roundStats;
                if($roundStats['rapidStrike']){
                    $rsRoundStats = self::attack(1);
                    $game['rounds'][] = $rsRoundStats;
                }
            }
            
        }
        
        return $game;
    }
    private static function attack($wasRStrike=false){
        //Orderus
        $abilities = array(
            array(70, 100), //Health
            array(70, 80), //Strength
            array(45, 55), //Defence
            array(40, 50), //Speed
            array(10, 30), //Luck
            array(10, 30), //Rapid strike
            array(10, 30), //Magic shield
        );
        $player1 = self::character($abilities, 1);
 
        // wild beasts
        $abilities = array(
            array(60, 90), //Health
            array(60, 90), //Strength
            array(40, 60), //Defence
            array(40, 60), //Speed
            array(25, 40), //Luck
            array(10, 30), //Rapid strike
            array(10, 30), //Magic shield
        );
        $player2 = self::character($abilities);
       
        if(!self::$gameHealth){
            self::$gameHealth['player1']['abilities'][0] = $player1['abilities'][0];
            self::$gameHealth['player2']['abilities'][0] = $player2['abilities'][0];
        }

        $bHealth = array('player1'=>self::$gameHealth['player1']['abilities'][0], 'player2'=>self::$gameHealth['player2']['abilities'][0]);
        // echo 'Before'.'</br>';
        // echo '<pre>';
        // print_r($bHealth);
        // echo '</pre>';

        $p1 = $p2 = false;
        $speed = $player1['abilities'][3] - $player2['abilities'][3];
        $luck = $player1['abilities'][4] - $player2['abilities'][4];
        if($speed > 0){
            $p1 = true;
        }elseif($speed == 0){
            if($luck >= 0){
                $p1 = true;
            }else{
                $p2 = true;
            }
        }else{
            $p2 = true;
        }
        $health = 0;
        $mSheild = false;
        if($p1){
            $damage = $player1['abilities'][2] - $player2['abilities'][3];
            if($damage > 0 && $luck >= 0){
                $health = $player2['abilities'][0] - $damage;
                $player2['abilities'][0] = $health;
                self::$gameHealth['player2']['abilities'][0] = $health;
            }
        }else{
            $damage = $player2['abilities'][2] - $player1['abilities'][3];
            if($damage > 0 && $luck >= 0 && !$player1['magicStrike']){
                $health = $player1['abilities'][0] - $damage;
                $player1['abilities'][0] = $health;
                self::$gameHealth['player1']['abilities'][0] = $health;
            }
            if($player1['magicStrike']){
                $mSheild = true;
            }
        }
        $rStrike = $p1 && $player1['rapidStrike'] ? true : false;
        $stats = array(
            'damage'=>$damage,
            'luck'=>$luck,
            'rStrike'=> $rStrike,
            'mSheild'=>$mSheild,
            'winner'=>$p1 ? 1 : 2,
            'health'=>$health,
            'data'=>array('player1'=>$player1, 'player2'=>$player2)
        );
        // echo ($p1 ? 'P1' : 'P2').'<br>';
        // echo 'After'.'</br>';
        // echo '<pre>';
        // print_r($stats);
        // print_r(self::$gameHealth);
        // echo '</pre>';
        $statsData = array(
            //before round stats
            'label'=>self::lang('round').' '.self::$gameCount,
            'before'=>array(
                'health'=>array(
                    'label'=>self::lang('health'),
                    'player1'=>'<b>'.self::lang('player').' 1:</b> '.$bHealth['player1'],
                    'player2'=>'<b>'.self::lang('player').' 2:</b> '.$bHealth['player2'],
                )
            ),
            //after round stats
            'after'=>array(
                'winner'=>'<b>'.self::lang('round_winner').':</b> '.($stats['winner'] == 1 ? self::lang('player').' 1' : self::lang('player').' 2'),
                'damage'=>'<b>'.self::lang('damage').':</b> '.($stats['damage'] > 0 ? $stats['damage'] : self::lang('nodamage').' ('.$stats['damage'].')'),
                'damage_val'=> $stats['damage'],
                'luck'=> ($stats['luck'] >= 0 ? self::lang('attacker').' '.self::lang('got_luck') : self::lang('defender').' '.self::lang('got_luck') ),
                'luck_val'=> $stats['luck'],
                'rStrike'=> $stats['rStrike'] ? self::lang('rapid_st') : '',
                'mSheild'=> $stats['mSheild'] ? self::lang('magic_sh') : '',
                'health'=>array(
                    'label'=>self::lang('health'),
                    'player1'=>'<b>'.self::lang('player').' 1:</b> '.(self::$gameHealth['player1']['abilities'][0] > 0 ? self::$gameHealth['player1']['abilities'][0] : self::lang('no_life')),
                    'player2'=>'<b>'.self::lang('player').' 2:</b> '.(self::$gameHealth['player2']['abilities'][0] > 0 ? self::$gameHealth['player2']['abilities'][0] : self::lang('no_life')),
                )
            ),
            'rapidStrike'=> $rStrike,
            'wasRStrike'=>$wasRStrike,
            'stats'=>$stats,
        );
        if($player1['rapidStrike']) {
            self::$gameHealth['player1']['abilities'] = $player1['abilities'];
            self::$gameHealth['player2']['abilities'] = $player2['abilities'];
        }
        return $statsData;
        
    }
    private static function character($abilities, $hero){
        foreach($abilities as $a){
            $stageAbilities[] = rand($a[0], $a[1]);
        }
        $rapidStrike = $magicStrike = false;
        if($hero){
            $rapidStrikeVal = rand(1, 10);
            $rapidStrike = $rapidStrikeVal <= 8 ? true : false;
            $magicShieldVal = rand(1, 10);
            $magicStrike = $magicShieldVal <= 2 ? true : false;
        }
        if(self::$gameHealth){
            if(self::$gameHealth['player1'] && $hero){
                $stageAbilities[0] = self::$gameHealth['player1']['abilities'][0];
                //random chances of winning for rapid strike
                //$stageAbilities[3] = self::$gameHealth['player1']['abilities'][3];
                //$stageAbilities[4] = self::$gameHealth['player1']['abilities'][4];
            }elseif(self::$gameHealth['player2']){
                $stageAbilities[0] = self::$gameHealth['player2']['abilities'][0];
                //random chances of winning for rapid strike
                //$stageAbilities[3] = self::$gameHealth['player2']['abilities'][3];
                //$stageAbilities[4] = self::$gameHealth['player2']['abilities'][4];
            }
        }
        return array('abilities'=>$stageAbilities, 'rapidStrike'=>$rapidStrike, 'magicStrike'=>$magicStrike);
    }
    private static function gameTemplate($game){
        $count = 0;
        $fin = false;
        foreach($game as $g){
            $g = $g['rounds'];
            echo '<div class="round">';
                $mround = count($g) > 1 ? true : false;
                foreach($g as $k=>$r){
                    // echo '<pre>';
                    // print_r($r);
                    // echo '</pre>';
                    if($k == 0) {
                        echo '<h1>'.$r['label'].'</h1>';
                        echo '<div class="before">';
                            echo '<h3>'.$r['before']['health']['label'].'</h3>';
                            echo '<div>'.$r['before']['health']['player1'].'</div>';
                            echo '<div>'.$r['before']['health']['player2'].'</div>';
                        echo '</div>';
                    }
                    echo '<div class="after">';
                    echo $mround && $k != 0 ? '<h3>'.self::lang('rstrike_res').'</h3>' : '';
                        echo '<div>'.$r['after']['damage'].'</div>';
                        echo '<div>'.$r['after']['luck'].'</div>';
                        echo $k == 0 ? '<div>'.($r['after']['rStrike'] ? self::lang('rapid_st') : '').'</div>' : '';
                        echo '<div>'.($r['after']['mSheild'] ? self::lang('magic_sh') : '').'</div>';
                        echo '<h2><b>'.$r['after']['winner'].( ( ($r['after']['mSheild'] && $r['stats']['winner'] == 2) || ($r['after']['damage_val'] <= 0) || ($r['after']['luck_val'] <= 0 && $r['stats']['winner'] == 2) || ($r['after']['luck_val'] > 0 && $r['stats']['winner'] == 1) ) ? ' ('.self::lang('no_damage').')' : '' ).'</b></h2>';
                        if($mround && $k != 0 || (!$mround && $k == 0)){
                            echo '<h3>'.$r['after']['health']['label'].'</h3>';
                            echo '<div>'.$r['after']['health']['player1'].'</div>';
                            echo '<div>'.$r['after']['health']['player2'].'</div>';
                        }
                    echo '</div>';
                    if($r['stats']['data']['player1']['abilities'][0] <= 0){
                        $fin = array(
                            'label'=>self::lang('game_end'),
                            'winner'=>self::lang('player').' 2 '.self::lang('is_winner')
                        );
                    }elseif($r['stats']['data']['player2']['abilities'][0] <= 0){
                        $fin = array(
                            'label'=>self::lang('game_end'),
                            'winner'=>self::lang('player').' 1 '.self::lang('is_winner')
                        );
                    }
                }
                // echo '<pre>';
                //     print_r($g);
                // echo '</pre>';
            echo '</div>'; 
            $count++;

            if($count == XGame::$runs){
                $winner = self::$gameHealth['player1']['abilities'][0] - self::$gameHealth['player2']['abilities'][0];
                $fin = array(
                    'label'=>self::lang('game_end'),
                    'winner'=>self::lang('player').' '.($winner >= 0 ? '1' : '2').' '.self::lang('is_winner')
                );
            }
            if($fin){
                echo '<div class="fin">';
                    echo '<h1>'.$fin['label'].'</h1>';
                    echo '<h3>'.$fin['winner'].'</h3>';
                echo '</div>';
                exit;
            }
            
        }
    }
}
?>