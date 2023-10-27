<?php 
namespace common\components;
 
 
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class EphocTime extends Component 
{
    public function time_elapsed_A($current,$create)
    {   
		$current_new = date("Y-m-d h:i:s A", $current);
		$create_new = date("Y-m-d h:i:s A", $create);
		
		$date1 = new \DateTime($current_new);
		$date2 = $date1->diff(new \DateTime($create_new));
		$date2->days.'Total days'."\n";
		$date2->y.' years'."\n";
		$date2->m.' months'."\n";
		$date2->d.' days'."\n";
		$date2->h.' hours'."\n";
		$date2->i.' minutes'."\n";
		$date2->s.' seconds'."\n";
		
        $cur_month = date("F");
        $cur_year = date("Y");
        $cur_day = date("d");
        $cre_month = date("F", $create);
        $cre_year = date("Y", $create);
        $cre_day = date("d", $create);
        $secs = $current - $create;
		
		if($cur_year != $cre_year)
		{
			$date2->y = 1;
		}
		
        $bit = array(
                        'year' => $date2->y,
                        'month' => $date2->m,
                        'days' => $date2->d,
                        'hrs' => $date2->h,
                        'mnts' => $date2->i,
                        'scnd' => $date2->s
                      //  ' secs' => $secs % 60
                    );
        //echo '<pre>';print_r($bit);
        if($bit['year'] >= 1)
        {
            return date("F d, Y", $create);
        }
        else if($bit['scnd'] > 0 && $bit['scnd'] <= 59 && $bit['mnts'] < 1 && $bit['hrs'] < 1 && $bit['days'] < 1 && $bit['year'] < 1)
        {
            return "Just now";
        }
		else if($bit['mnts'] >= 1  && $bit['hrs'] < 1 && $bit['days'] < 1 && $bit['year'] < 1)
        {	if($bit['mnts'] > 1)
			{	
				return $bit['mnts']. ' mins';
			}
			else
			{
				return $bit['mnts']. ' min';
			}	
			
        }
		else if($bit['hrs'] >= 1 && $bit['hrs'] < 24  && $bit['days'] < 1 && $bit['year'] < 1)
        {
			if($bit['hrs'] > 1)
			{
				return $bit['hrs']. ' hrs';
			}
			else
			{
				return $bit['hrs']. ' hr';
			}
        }
		else if($bit['days'] >= 1 && $bit['days'] < 2  && $bit['year'] < 1)
        {
			return 'Yesterday at ' .date("g:i a", $create);	
        }
        else if($bit['days'] >= 2 && $bit['year'] < 1 )
        {
            return date("F d", $create). ' at ' .date("g:i a", $create);
        }
        else
        {
          //$ret[] = $v . $k;
            foreach($bit as $k => $v) 
            {
                if($v > 0) 
                {
                  $ret[] = $v . $k;
                }
            }
        }
        if(empty($ret))
        {
            return "Just now";
        }
        else
        {
            return join(' ', $ret);
        }
    }
    
    public function time_pwd_changed($current,$create)
    {
        $cur_month = date("F");
        $cur_year = date("Y");
        $cur_day = date("d");
        $cre_month = date("F", $create);
        $cre_year = date("Y", $create);
        $cre_day = date("d", $create);
        $secs = $current - $create;
        $bit = array(
                        ' y' => $secs / 31556926 % 12,
                        ' w' => $secs / 604800 % 52,
                        ' d' => $secs / 86400 % 7,
                        ' h' => $secs / 3600 % 24,
                        ' m' => $secs / 60 % 60
                      //  ' secs' => $secs % 60
                    );
        //echo '<pre>';print_r($bit);
        foreach($bit as $k => $v) 
        {
            if($v > 0) 
            {
              $ret[] = $v . $k;
            }
        }
        if(empty($ret))
        {
            return "just now";
        }
        else
        {
            return join(' ', $ret).' ago';
        }
    }
	
	public function comment_time($current,$create)
    {
        $current_new = date("Y-m-d h:i:s", $current);
		$create_new = date("Y-m-d h:i:s", $create);
		
		$date1 = new \DateTime($current_new);
		$date2 = $date1->diff(new \DateTime($create_new));
		$date2->days.'Total days'."\n";
		$date2->y.' years'."\n";
		$date2->m.' months'."\n";
		$date2->d.' days'."\n";
		$date2->h.' hours'."\n";
		$date2->i.' minutes'."\n";
		$date2->s.' seconds'."\n";
		
        $cur_month = date("F");
        $cur_year = date("Y");
        $cur_day = date("d");
        $cre_month = date("F", $create);
        $cre_year = date("Y", $create);
        $cre_day = date("d", $create);
        $secs = $current - $create;
		
		if($cur_year != $cre_year)
		{
			$date2->y = 1;
		}
		
        $bit = array(
                        'year' => $date2->y,
                        'month' => $date2->m,
                        'days' => $date2->d,
                        'hrs' => $date2->h,
                        'mnts' => $date2->i,
                        'scnd' => $date2->s
                      //  ' secs' => $secs % 60
                    );
        //echo '<pre>';print_r($bit);
        if($bit['year'] >= 1)
        {
            return date("F d, Y", $create);
        }
        else if($bit['scnd'] > 0 && $bit['scnd'] <= 59 && $bit['mnts'] < 1 && $bit['hrs'] < 1 && $bit['days'] < 2 && $bit['year'] < 1)
        {
            return "Just now";
        }
		else if($bit['mnts'] >= 1  && $bit['hrs'] < 1 && $bit['days'] < 1 && $bit['year'] < 1)
        {		
				return $bit['mnts']. ' mins';
			
        }
		else if($bit['hrs'] >= 1 && $bit['hrs'] < 24  && $bit['days'] < 1 && $bit['year'] < 1)
        {
			if($bit['hrs'] > 1)
			{
				return $bit['hrs']. ' hrs';
			}
			else
			{
				return $bit['hrs']. ' hr';
			}
        }
        else if($bit['days'] >= 1 && $bit['year'] < 1 )
        {
            return date("M d", $create);
            //return date("M d", $create). ' at ' .date("g:i a", $create);
        }
        else
        {
          //$ret[] = $v . $k;
            foreach($bit as $k => $v) 
            {
                if($v > 0) 
                {
                  $ret[] = $v . $k;
                }
            }
        }
        if(empty($ret))
        {
            return "Just now";
        }
        else
        {
            return join(' ', $ret);
        }
    }
	
}