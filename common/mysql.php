<?php
// 数据库连接
function db_connect($host=DB_HOST,$user=DB_USER,$pwd=DB_PWD,$database=DB_NAME,$port=DB_PORT){
    $link = @mysqli_connect($host,$user,$pwd,$database,$port);
    $errcode = mysqli_connect_errno();
    if($errcode){
        $errmsg = '
            Database Connect Error!<br />
            Error Code: '.$errcode.'<br />
            Error message: '.mysqli_connect_error().'<br />
        ';
        die($errmsg);
    }
    mysqli_set_charset ( $link , 'utf8' ); 
    return $link;
}

// 执行SQL语句，返回结果集或布尔值
function db_exec($link,$query){
    $result = mysqli_query($link,$query);
    $errcode = mysqli_errno($link);
    if ($errcode){
        $errmsg = '
            SQL Query Error!<br />
            Error Code: '.$errcode.'<br />
            Error message: '.mysqli_error($link).'<br />
        ';
        die($errmsg);
    }
    return $result;
}

// 执行SQL语句，只返回布尔值
function db_exec_bool($link,$query){
    $result = mysqli_real_query($link,$query);
    $errcode = mysqli_errno($link);
    if ($errcode){
        $errmsg = '
            SQL Query Error!<br />
            Error Code: '.$errcode.'<br />
            Error message: '.mysqli_error($link).'<br />
        ';
        die($errmsg);
    }
    return $result;
}

// 执行多条SQL语句
function db_multiexec($link,$arr_sqls,&$results,&$error){
    if (is_string($arr_sqls)) {
        $results=mysqli_fetch_all(db_exec($link,$arr_sqls));
        return true;
    }
    $results = []; //初始化结果集数组
    $i = 0; //计数
    
    $sqls = implode(';',$arr_sqls).';';
    if(mysqli_multi_query($link,$sqls)){
        do{
            if ($temp = mysqli_store_result($link)){
                $results[$i] = mysqli_fetch_all($temp);
                mysqli_free_result($temp);
            } else {
                $results[$i] = null;
            }
            $i++;

            /* 如果还有更多结果，可以做写处理
            if(mysqli_more_result($link)){

            }
            */

            // 每执行完一条SQL语句，都要执行mysqli_next_result($link)，
            // 才能通过mysqli_store_result($link)获得下一条语句的结果
        }while(mysqli_next_result($link));
    }
    if ($i == count($arr_sqls)){
        return true;
    } else {
        $errcode = mysqli_errno($link);
        $error = '
            The No.'.($i+1).' SQL Query Error!<br />
            SQL Query: '.$arr_sqls[$i].'<br />
            Error Code: '.$errcode.'<br />
            Error message: '.mysqli_error($link).'<br />
            The previous '.$i.' results are stored.<br />
        ';
        return false;

    }

}

// 获取记录数
function record_count($link,$query){
    $result = db_exec($link,$query);
    $count = mysqli_num_rows($result);
    return $count;
}

// 字符串转义，支持多维数组
function real_string($link,$strings){
    if(is_string($strings)){
        $real_strings=mysqli_real_escape_string($link,$strings);
    }
    if(is_array($strings)){
        foreach($strings as $k => $v){
            $real_strings[$k] = real_string($link,$v);
        }
    }
    return $real_strings;
}

// 查询字符宽度
function char_max_len($link,$table,$column){
    $query = 'select '.$column.' from '.$table;
    $result = db_exec($link,$query);

    $finfo = mysqli_fetch_field_direct($result,0);
    // 返回按字节计算的字符宽度，如果是utf8编码，每个字符占三个字节，所以字符数是length/3
    $char_max_num = ($finfo->length)/3;

    mysqli_free_result($result);

    return $char_max_num;
}

// 关闭数据库连接
function db_close($link){
    mysqli_close($link);
}



?>