<?php
function requestm(
    string $url, array $headers,
    string $data = null, string $method = "GET"
) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADER, false);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($data != null) curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    switch ($httpcode) {
		case 400:
            throw new Exception('Limit');
            break;
			
        case 401:
            throw new Exception('Unauthorized');
            break;
            
        case 403:
            throw new Exception('No access');
            break;
    }

    $o = array(
        "content" => $response,
        "status_code" => $httpcode
    );

    return $o;
}

class fastpanel {
    protected string $token;
    protected array $headers;
    protected string $host;

    public function __construct(string $host) {
        $this->host = "https://".$host;
    }

    public function login(string $login, string $password) {
        $body = json_encode(
            array(
                "username" => $login,
                "password" => $password
            )
        );

        $head = [
            "User-Agent: fastpanel.php"
        ];

        $request = requestm("$this->host/login", $head, $body, "POST");

        $content = json_decode($request['content'], true);

        $this->token = $content['token'];
        $this->headers = [
            "User-Agent: fastpanel.php",
            "Authorization: Bearer ".$this->token
        ];

        return true;
    }

    public function account_information() {
        $request = requestm(
            "$this->host/api/me",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content'], true);
        return $content['data'];
    }

    // users

    public function user_information(int $id) {
        $request = requestm(
            "$this->host/api/users/$id",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function users() {
        $request = requestm(
            "$this->host/api/users",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function create_user(
        string $username, string $password, 
        string $role, int $quota
    ) {
        $body = json_encode(
            array(
                "username" => $username,
                "password" => $password,
                "roles" => $role,
                "quota" => $quota
            )
        );

        $request = requestm(
            "$this->host/api/users",
            $this->headers,
            $body, "POST"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function enable_user(int $id, bool $enabled) {
        $body = json_encode(
            array(
                "enabled" => $enabled
            )
        );

        $request = requestm(
            "$this->host/api/users/$id/status",
            $this->headers,
            $body, "POST"
        );

        return true;
    }

    public function delete_user(int $id) {
        $request = requestm(
            "$this->host/api/users/$id",
            $this->headers,
            null, "DELETE"
        );

        return true;
    }

    public function change_user_password(int $id, string $password) {
        $body = json_encode(
            array(
                "password" => $password
            )
        );

        $request = requestm(
            "$this->host/api/users/$id",
            $this->headers,
            $body, "PUT"
        );

        return true;
    }

    public function change_user_quota(int $id, int $quota) {
        $body = json_encode(
            array(
                "quota" => $quota
            )
        );

        $request = requestm(
            "$this->host/api/users/$id",
            $this->headers,
            $body, "PUT"
        );

        return true;
    }

    public function change_user_ssh(int $id, bool $enabled) {
        $body = json_encode(
            array(
                "ssh_access" => $enabled
            )
        );

        $request = requestm(
            "$this->host/api/users/$id",
            $this->headers,
            $body, "PUT"
        );

        return true;
    }

    // sites

    public function sites() {
        $request = requestm(
            "$this->host/api/sites/simple",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content']);
        return $content['data'];
    }

    public function create_site(
        array $aliases, int $backup_plan_id,
        array $database, array $dns_domain,
        string $domain, bool $email_domain,
        array $ftp_account, array $ips, string $mode,
        int $owner, int $php_version, array $sftp_account,
        bool $ssh_access, int $user
    ) {
        $body = json_encode(
            array(
                "aliases" => $aliases,
                "domain" => $domain,
                "email_domain" => $email_domain,
                "ips" => $ips,
                "dns_domain" => $dns_domain,
                "owner" => $owner,
                "ssh_access" => $ssh_access,
                "user" => $user,
                "database" => $database,
                "mode" => $mode,
                "php_version" => $php_version,
                "ftp_account" => $ftp_account,
                "sftp_account" => $sftp_account,
                "backup_plan_id" => $backup_plan_id
            )
        );

        $request = requestm(
            "$this->host/api/master",
            $this->headers,
            $body, "PUT"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function enable_site(int $id, bool $enabled) {
        $body = json_encode(
            array(
                "enabled" => $enabled
            )
        );

        $request = requestm(
            "$this->host/api/sites/$id/status",
            $this->headers,
            $body, "PUT"
        );

        return true;
    }

    public function delete_site(int $id) {
        $request = requestm(
            "$this->host/api/sites/$id/status",
            $this->headers,
            null, "DELETE"
        );

        return true;
    }

    # email

    public function boxs(int $id) {
        $request = requestm(
            "$this->host/api/email/domains/$id/boxs",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content']);
        return $content['data'];
    }

    public function create_box(
        int $id, int $quota,
        string $login, string $password,
        bool $spam_to_junk, array $redirects,
        array $aliases
    ) {
        $body = json_encode(
            array(
                "aliases" => $aliases,
                "login" => $login,
                "password" => $password,
                "spam_to_junk" => $spam_to_junk,
                "quota" => $quota,
                "redirects" => $redirects
            )
        );

        $request = requestm(
            "$this->host/api/email/domains/$id/boxs",
            $this->headers,
            $body, "POST"
        );
        
        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function change_box_password(int $id, string $password) {
        $body = json_encode(
            array(
                "password" => $password
            )
        );

        $request = requestm(
            "$this->host/api/mail/box/$id",
            $this->headers,
            $body, "PUT"
        );

        return true;
    }

    public function enable_box(int $id, bool $enable) {
        $body = json_encode(
            array(
                "enable" => $enable
            )
        );

        $request = requestm(
            "$this->host/api/mail/box/$id",
            $this->headers,
            $body, "PUT"
        );

        return true;
    }

    // ftp
    
    public function ftps() {
        $request = requestm(
            "$this->host/api/ftp/accounts",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function create_ftp_user(
        string $home_dir, string $name,
        string $password, int $limit, int $owner
    ) {
        $body = json_encode(
            array(
                "enabled" => true,
                "home_dir" => $home_dir,
                "limit" => $limit,
                "name" => $name,
                "owner" => $owner,
                "password" => $password
            )
        );

        $request = requestm(
            "$this->host/api/ftp/accounts",
            $this->headers,
            $body, "POST"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function delete_ftp_user(int $id) {
        $request = requestm(
            "$this->host/api/ftp/$id/accounts",
            $this->headers,
            null, "DELETE"
        );

        return true;
    }

    // database

    public function dbs() {
        $request = requestm(
            "$this->host/api/databases",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function db_users(int $id) {
        $request = requestm(
            "$this->host/api/databases/$id/users",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function create_db(
        string $charset, string $name, int $owner_id,
        int $server_id, int $site, array $user
    ) {
        $body = json_encode(
            array(
                "charset" => $charset,
                "name" => $name,
                "owner_id" => $owner_id,
                "server_id" => $server_id,
                "site" => $site,
                "user" => $user
            )
        );

        $request = requestm(
            "$this->host/api/databases",
            $this->headers,
            $body, "POST"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function create_db_user(
        int $id, string $login, string $password,
        bool $allow_remote_connection
    ) {
        $body = json_encode(
            array(
                "allow_remote_connection" => $allow_remote_connection,
                "login" => $login,
                "password" => $password
            )
        );

        $request = requestm(
            "$this->host/api/databases/users/$id",
            $this->headers,
            $body, "POST"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function change_db_user_password(
        int $id, string $password
    ) {
        $body = json_encode(
            array(
                "user" => array(
                    "password" => $password
                )
            )
        );
        
        $request = requestm(
            "$this->host/api/databases/users/$id",
            $this->headers,
            $body, "PUT"
        );

        return true;
    }

    public function delete_db(int $id) {
        $request = requestm(
            "$this->host/api/databases/$id",
            $this->headers,
            null, "DELETE"
        );

        return true;
    }

    // cron

    public function crons() {
        $request = requestm(
            "$this->host/api/jobs",
            $this->headers,
            null, "GET"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function create_cron(
        string $command, bool $enabled,
        string $day_of_month, string $day_of_week,
        string $hour, string $minute,
        string $month, int $owner
    ) {
        $body = json_encode(
            array(
                "command" => $command,
                "day_of_month" => $day_of_month,
                "day_of_week" => $day_of_week,
                "enabled" => $enabled,
                "hour" => $hour,
                "minute" => $minute,
                "month" => $month,
                "owner" => $owner,
            )
        );

        $request = requestm(
            "$this->host/api/jobs",
            $this->headers,
            $body, "POST"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function edit_cron(
        string $command, bool $enabled,
        string $day_of_month, string $day_of_week,
        string $hour, string $minute,
        string $month, int $id
    ) {
        $body = json_encode(
            array(
                "command" => $command,
                "day_of_month" => $day_of_month,
                "day_of_week" => $day_of_week,
                "enabled" => $enabled,
                "hour" => $hour,
                "minute" => $minute,
                "month" => $month,
            )
        );

        $request = requestm(
            "$this->host/api/jobs/$id",
            $this->headers,
            $body, "POST"
        );

        $content = json_decode($request['content'], true);

        return $content['data'];
    }

    public function start_cron(int $id) {
        $request = requestm(
            "$this->host/api/jobs/$id/start",
            $this->headers,
            null, "PUT"
        );

        return true;
    }

    public function delete_cron(int $id) {
        $request = requestm(
            "$this->host/api/jobs/$id",
            $this->headers,
            null, "DELETE"
        );

        return true;
    }
}
?>