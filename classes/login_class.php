<?php
class Login {

    public static function isLoggedIn() {
        if(isset($_COOKIE['FCID'])) {
			
            if (DB::query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['FCID'])))) {
                    $userid = DB::query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['FCID'])))[0]['user_id'];            
                    
                    if (isset($_COOKIE['FCID_'])) {
                        return $userid;
                    } else {
                        $cstrong = TRUE;
                        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                        DB::query('INSERT INTO login_tokens VALUES (\'\', :token, :user_id)', array(':token'=>sha1($token), ':user_id'=>$userid));
                        DB::query('DELETE FROM login_tokens WHERE token=:token', array(':token'=>sha1($_COOKIE['FCID'])));
    
                        setcookie("FCID", $token, time() + 60 * 60 * 24 * 7, "/", NULL, NULL, TRUE);
                        setcookie("FCID_", '1', time() + 60 * 60 * 24 * 3, "/", NULL, NULL, TRUE);
    
                        return $userid;
                    }
            }
        }
        
        return false;
    }

}
?>