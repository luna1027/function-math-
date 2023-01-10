<?php
date_default_timezone_set("Asia/Taipei");
session_start();

class DB
{
    protected $dsn = "mysql:host=localhost;charset=utf8;dbname=db19"; // dbname 記得改成自己的資料庫名稱
    protected $table;
    protected $pdo;

    public function __construct($table)
    {
        $this->table = $table;
        $this->pdo = new PDO($this->dsn, 'root', '');
    }

    public function count(...$arg)
    {
        return $this->math('count', ...$arg); //...為解構賦值
    }

    public function sum($col, ...$arg)
    { //...為不定參數
        return $this->math('sum', $col, ...$arg); //...為解構賦值
    }
    public function max($col, ...$arg)
    {
        return $this->math('max', $col, ...$arg); //...為解構賦值
    }
    public function min($col, ...$arg)
    {
        return $this->math('min', $col, ...$arg); //...為解構賦值
    }
    public function avg($col, ...$arg)
    {
        return $this->math('avg', $col, ...$arg); //...為解構賦值
    }

    private function arrayToSqlArray($array)
    {
        foreach ($array as $key => $value) {
            $tmp[] = "`$key`='$value'";
        }

        return $tmp;
    }

    // 只要變數前面加上 "..." 則為不定參數，會自動將變數放進陣列裡
    // 所以 ...$arg => 一定是陣列
    private function math($math, ...$arg)
    {
        // 用 switch 來區分 "count()" 及其他四個運算函式
        switch ($math) {
            case 'count':  // $math 變數為 'count' 時
                $sql = "select count(*) from `$this->table` "; // 設定 $sql 變數為 count() 的 SQL語句
                if (isset($arg[0])) { // *如果有收到陣列 $arg[0] => 這邊直接取 $arg[0] 是因為條件一定放在 key 值為 0 的位置 ( 第48行有解釋原因 )
                    $con = $arg[0];  // 不需判斷是字串或陣列，先將他放進一個新的變數 $con 裡，之後直接使用該變數 => 降低錯誤率及統一變數方便使用
                }
                break;
                // switch 分隔線 //
            default: // $math 為其他內容時 ( max、min、avg、sum ) 就跑這邊
                $col = $arg[0]; // 這四種計算方式皆有要求指定 $cal => sql 資料表裡的欄位名稱，所以在經過 ...$arg 之後會自動放入 $arg[0] 的位置，此時再把他拿出來放入新的變數 $col 裡
                if (isset($arg[1])) { // *如果有收到陣列 $arg 第二個位置的 value ( key 值為 1 )
                    $con = $arg[1]; // 在經過 ...$arg 之後，原始 function 的 ...$arg (ex: "public function avg($col, ...$arg)" 的 $...arg )會自動放入 $arg[1] 的位置，此時再把他拿出來放進一個新的變數 $con 裡，之後直接使用該變數 => 降低錯誤率及統一變數方便使用
                }
                $sql = "select $math($col) from `$this->table` "; // 設定 $sql 變數為 ( max、min、avg、sum ) 的 SQL語句

        }

        // 上面判斷完是 count 或 ( max、min、avg、sum ) 後再跑這邊
        if (isset($con)) { // 如果有收到條件 => 來源: count() 從第57行 、 其他從第64行
            if (is_array($con)) { // 判斷是否為陣列
                // 是陣列
                $tmp = $this->arrayToSqlArray($con); // 用 function arrayToSqlArray() 轉成 SQL 需要的語句 => 第39行
                $sql = $sql . " where " .  join(" && ", $tmp); // 將他放進 $sql => SQL語句的最後
            } else {
                // 不是陣列
                $sql = $sql . $con; // 直接放進 $sql => SQL語句的最後
            }
        }
        echo $sql; // debug很好用，看不懂code時也很好用
        return $this->pdo->query($sql)->fetchColumn(); // 因為都是運算式，只需用 fetchColumn() 回傳數字
    }
}

function dd($array)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

$Title = new DB('title'); // 記得改成自己有的資料表
$Title->avg('`id`'); // 這邊隨便測的，需要看計算出來的答案就 echo
