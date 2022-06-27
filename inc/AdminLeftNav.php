<?php
 class AdminLeftNav{

     /**
     * prepare()
     * 
     * This method has dynamic argument(s).
     * 
     * Arguments- (1)string $role (2)string $baseUrl
     */

    public static function CreateFor(string $role, string $baseUrl, string $session_id = ""){
        $numberOfArguments = func_num_args();
        $arguments = func_get_args();
        $role = $arguments[0]; 
        $baseUrl = $arguments[1]; 
        $leftNav = "";

        if(strtolower($role) == "superadmin"){
            $leftNav = <<<HTML
                <ul>
                    <li><a href="$baseUrl/admins/dashboard.php?session-id=$session_id"><span class="m-icons">dashboard</span>Dashboard</a></li>
                    <li><a href="$baseUrl/app/create/create-task.php?session-id=$session_id"><span class="m-icons">dashboard</span>Create Task</a></li>

                    <li><a href="#"><span class="m-icons">auto_stories</span>Progress</a>
                        <ul>
                            <li>
                                <a href="$baseUrl/app/tasks/progress.php?session-id=$session_id"><span class="m-icons">format_list_numbered</span>View</a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="$baseUrl/admins/logout.php?session-id=$session_id"><span class="m-icons">logout</span>Logout</a>
                    </li>
                </ul>
            HTML;
        }
        return $leftNav;
    }
}
?>


