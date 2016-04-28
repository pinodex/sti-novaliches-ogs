
(function(root) {

    var bhIndex = null;
    var rootPath = '';
    var treeHtml = '        <ul>                <li data-name="namespace:App" class="opened">                    <div style="padding-left:0px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App.html">App</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:App_Constraints" class="opened">                    <div style="padding-left:18px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Constraints.html">Constraints</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Constraints_HasRecord" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Constraints/HasRecord.html">HasRecord</a>                    </div>                </li>                            <li data-name="class:App_Constraints_HasRecordValidator" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Constraints/HasRecordValidator.html">HasRecordValidator</a>                    </div>                </li>                            <li data-name="class:App_Constraints_UniqueRecord" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Constraints/UniqueRecord.html">UniqueRecord</a>                    </div>                </li>                            <li data-name="class:App_Constraints_UniqueRecordValidator" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Constraints/UniqueRecordValidator.html">UniqueRecordValidator</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Controllers" class="opened">                    <div style="padding-left:18px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Controllers.html">Controllers</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:App_Controllers_Admin" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Controllers/Admin.html">Admin</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Controllers_Admin_MainController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Admin/MainController.html">MainController</a>                    </div>                </li>                            <li data-name="class:App_Controllers_Admin_ManageAdminController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Admin/ManageAdminController.html">ManageAdminController</a>                    </div>                </li>                            <li data-name="class:App_Controllers_Admin_ManageFacultyController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Admin/ManageFacultyController.html">ManageFacultyController</a>                    </div>                </li>                            <li data-name="class:App_Controllers_Admin_ManageStudentController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Admin/ManageStudentController.html">ManageStudentController</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Controllers_Faculty" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Controllers/Faculty.html">Faculty</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Controllers_Faculty_GradesController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Faculty/GradesController.html">GradesController</a>                    </div>                </li>                            <li data-name="class:App_Controllers_Faculty_MainController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Faculty/MainController.html">MainController</a>                    </div>                </li>                            <li data-name="class:App_Controllers_Faculty_StudentController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Faculty/StudentController.html">StudentController</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Controllers_Student" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Controllers/Student.html">Student</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Controllers_Student_MainController" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Controllers/Student/MainController.html">MainController</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:App_Controllers_MainController" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Controllers/MainController.html">MainController</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Models" class="opened">                    <div style="padding-left:18px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Models.html">Models</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Models_Admin" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Models/Admin.html">Admin</a>                    </div>                </li>                            <li data-name="class:App_Models_Faculty" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Models/Faculty.html">Faculty</a>                    </div>                </li>                            <li data-name="class:App_Models_Grade" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Models/Grade.html">Grade</a>                    </div>                </li>                            <li data-name="class:App_Models_Session" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Models/Session.html">Session</a>                    </div>                </li>                            <li data-name="class:App_Models_Student" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Models/Student.html">Student</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Providers" class="opened">                    <div style="padding-left:18px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Providers.html">Providers</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Providers_IlluminateDatabaseServiceProvider" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Providers/IlluminateDatabaseServiceProvider.html">IlluminateDatabaseServiceProvider</a>                    </div>                </li>                            <li data-name="class:App_Providers_TwigExtensionServiceProvider" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Providers/TwigExtensionServiceProvider.html">TwigExtensionServiceProvider</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Routes" class="opened">                    <div style="padding-left:18px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Routes.html">Routes</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:App_Routes_Admin" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Routes/Admin.html">Admin</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Routes_Admin_MainRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Admin/MainRoute.html">MainRoute</a>                    </div>                </li>                            <li data-name="class:App_Routes_Admin_ManageAdminRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Admin/ManageAdminRoute.html">ManageAdminRoute</a>                    </div>                </li>                            <li data-name="class:App_Routes_Admin_ManageFacultyRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Admin/ManageFacultyRoute.html">ManageFacultyRoute</a>                    </div>                </li>                            <li data-name="class:App_Routes_Admin_ManageStudentRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Admin/ManageStudentRoute.html">ManageStudentRoute</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Routes_Faculty" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Routes/Faculty.html">Faculty</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Routes_Faculty_GradesRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Faculty/GradesRoute.html">GradesRoute</a>                    </div>                </li>                            <li data-name="class:App_Routes_Faculty_MainRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Faculty/MainRoute.html">MainRoute</a>                    </div>                </li>                            <li data-name="class:App_Routes_Faculty_StudentRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Faculty/StudentRoute.html">StudentRoute</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Routes_Student" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Routes/Student.html">Student</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Routes_Student_MainRoute" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Routes/Student/MainRoute.html">MainRoute</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:App_Routes_MainRoute" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Routes/MainRoute.html">MainRoute</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Services" class="opened">                    <div style="padding-left:18px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Services.html">Services</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:App_Services_Auth" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Services/Auth.html">Auth</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="namespace:App_Services_Auth_Provider" >                    <div style="padding-left:54px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Services/Auth/Provider.html">Provider</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Services_Auth_Provider_AdminProvider" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="App/Services/Auth/Provider/AdminProvider.html">AdminProvider</a>                    </div>                </li>                            <li data-name="class:App_Services_Auth_Provider_AuthProviderInterface" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="App/Services/Auth/Provider/AuthProviderInterface.html">AuthProviderInterface</a>                    </div>                </li>                            <li data-name="class:App_Services_Auth_Provider_FacultyProvider" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="App/Services/Auth/Provider/FacultyProvider.html">FacultyProvider</a>                    </div>                </li>                            <li data-name="class:App_Services_Auth_Provider_StudentProvider" >                    <div style="padding-left:80px" class="hd leaf">                        <a href="App/Services/Auth/Provider/StudentProvider.html">StudentProvider</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:App_Services_Auth_User" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Services/Auth/User.html">User</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="namespace:App_Services_Session" >                    <div style="padding-left:36px" class="hd">                        <span class="glyphicon glyphicon-play"></span><a href="App/Services/Session.html">Session</a>                    </div>                    <div class="bd">                                <ul>                <li data-name="class:App_Services_Session_EloquentSessionHandler" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Services/Session/EloquentSessionHandler.html">EloquentSessionHandler</a>                    </div>                </li>                            <li data-name="class:App_Services_Session_FlashBag" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Services/Session/FlashBag.html">FlashBag</a>                    </div>                </li>                            <li data-name="class:App_Services_Session_Session" >                    <div style="padding-left:62px" class="hd leaf">                        <a href="App/Services/Session/Session.html">Session</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:App_Services_Auth" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/Auth.html">Auth</a>                    </div>                </li>                            <li data-name="class:App_Services_Csrf" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/Csrf.html">Csrf</a>                    </div>                </li>                            <li data-name="class:App_Services_Form" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/Form.html">Form</a>                    </div>                </li>                            <li data-name="class:App_Services_GradingSheet" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/GradingSheet.html">GradingSheet</a>                    </div>                </li>                            <li data-name="class:App_Services_Hash" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/Hash.html">Hash</a>                    </div>                </li>                            <li data-name="class:App_Services_Helper" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/Helper.html">Helper</a>                    </div>                </li>                            <li data-name="class:App_Services_OmegaSheet" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/OmegaSheet.html">OmegaSheet</a>                    </div>                </li>                            <li data-name="class:App_Services_Service" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/Service.html">Service</a>                    </div>                </li>                            <li data-name="class:App_Services_View" >                    <div style="padding-left:44px" class="hd leaf">                        <a href="App/Services/View.html">View</a>                    </div>                </li>                </ul></div>                </li>                            <li data-name="class:App_App" class="opened">                    <div style="padding-left:26px" class="hd leaf">                        <a href="App/App.html">App</a>                    </div>                </li>                </ul></div>                </li>                </ul>';

    var searchTypeClasses = {
        'Namespace': 'label-default',
        'Class': 'label-info',
        'Interface': 'label-primary',
        'Trait': 'label-success',
        'Method': 'label-danger',
        '_': 'label-warning'
    };

    var searchIndex = [
                    
            {"type": "Namespace", "link": "App.html", "name": "App", "doc": "Namespace App"},{"type": "Namespace", "link": "App/Constraints.html", "name": "App\\Constraints", "doc": "Namespace App\\Constraints"},{"type": "Namespace", "link": "App/Controllers.html", "name": "App\\Controllers", "doc": "Namespace App\\Controllers"},{"type": "Namespace", "link": "App/Controllers/Admin.html", "name": "App\\Controllers\\Admin", "doc": "Namespace App\\Controllers\\Admin"},{"type": "Namespace", "link": "App/Controllers/Faculty.html", "name": "App\\Controllers\\Faculty", "doc": "Namespace App\\Controllers\\Faculty"},{"type": "Namespace", "link": "App/Controllers/Student.html", "name": "App\\Controllers\\Student", "doc": "Namespace App\\Controllers\\Student"},{"type": "Namespace", "link": "App/Models.html", "name": "App\\Models", "doc": "Namespace App\\Models"},{"type": "Namespace", "link": "App/Providers.html", "name": "App\\Providers", "doc": "Namespace App\\Providers"},{"type": "Namespace", "link": "App/Routes.html", "name": "App\\Routes", "doc": "Namespace App\\Routes"},{"type": "Namespace", "link": "App/Routes/Admin.html", "name": "App\\Routes\\Admin", "doc": "Namespace App\\Routes\\Admin"},{"type": "Namespace", "link": "App/Routes/Faculty.html", "name": "App\\Routes\\Faculty", "doc": "Namespace App\\Routes\\Faculty"},{"type": "Namespace", "link": "App/Routes/Student.html", "name": "App\\Routes\\Student", "doc": "Namespace App\\Routes\\Student"},{"type": "Namespace", "link": "App/Services.html", "name": "App\\Services", "doc": "Namespace App\\Services"},{"type": "Namespace", "link": "App/Services/Auth.html", "name": "App\\Services\\Auth", "doc": "Namespace App\\Services\\Auth"},{"type": "Namespace", "link": "App/Services/Auth/Provider.html", "name": "App\\Services\\Auth\\Provider", "doc": "Namespace App\\Services\\Auth\\Provider"},{"type": "Namespace", "link": "App/Services/Session.html", "name": "App\\Services\\Session", "doc": "Namespace App\\Services\\Session"},
            {"type": "Interface", "fromName": "App\\Services\\Auth\\Provider", "fromLink": "App/Services/Auth/Provider.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "doc": "&quot;Interface for auth providers&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getRole", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getRole", "doc": "&quot;Get provider account role&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getRedirectRoute", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getRedirectRoute", "doc": "&quot;Get route to redirect after login&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getAllowedControllers", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getAllowedControllers", "doc": "&quot;Get protected route group the user has access to&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getName", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getName", "doc": "&quot;Get user name&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_attempt", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::attempt", "doc": "&quot;Attempt to login&quot;"},
            
            
            {"type": "Class", "fromName": "App", "fromLink": "App.html", "link": "App/App.html", "name": "App\\App", "doc": "&quot;Main application&quot;"},
                    
            {"type": "Class", "fromName": "App\\Constraints", "fromLink": "App/Constraints.html", "link": "App/Constraints/HasRecord.html", "name": "App\\Constraints\\HasRecord", "doc": "&quot;&quot;"},
                    
            {"type": "Class", "fromName": "App\\Constraints", "fromLink": "App/Constraints.html", "link": "App/Constraints/HasRecordValidator.html", "name": "App\\Constraints\\HasRecordValidator", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "App\\Constraints\\HasRecordValidator", "fromLink": "App/Constraints/HasRecordValidator.html", "link": "App/Constraints/HasRecordValidator.html#method_validate", "name": "App\\Constraints\\HasRecordValidator::validate", "doc": "&quot;{@inheritDoc}&quot;"},
            
            {"type": "Class", "fromName": "App\\Constraints", "fromLink": "App/Constraints.html", "link": "App/Constraints/UniqueRecord.html", "name": "App\\Constraints\\UniqueRecord", "doc": "&quot;&quot;"},
                    
            {"type": "Class", "fromName": "App\\Constraints", "fromLink": "App/Constraints.html", "link": "App/Constraints/UniqueRecordValidator.html", "name": "App\\Constraints\\UniqueRecordValidator", "doc": "&quot;&quot;"},
                                                        {"type": "Method", "fromName": "App\\Constraints\\UniqueRecordValidator", "fromLink": "App/Constraints/UniqueRecordValidator.html", "link": "App/Constraints/UniqueRecordValidator.html#method_validate", "name": "App\\Constraints\\UniqueRecordValidator::validate", "doc": "&quot;{@inheritDoc}&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Admin", "fromLink": "App/Controllers/Admin.html", "link": "App/Controllers/Admin/MainController.html", "name": "App\\Controllers\\Admin\\MainController", "doc": "&quot;Admin controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Admin\\MainController", "fromLink": "App/Controllers/Admin/MainController.html", "link": "App/Controllers/Admin/MainController.html#method_index", "name": "App\\Controllers\\Admin\\MainController::index", "doc": "&quot;Admin page index&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Admin", "fromLink": "App/Controllers/Admin.html", "link": "App/Controllers/Admin/ManageAdminController.html", "name": "App\\Controllers\\Admin\\ManageAdminController", "doc": "&quot;Admin manage admin controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageAdminController", "fromLink": "App/Controllers/Admin/ManageAdminController.html", "link": "App/Controllers/Admin/ManageAdminController.html#method_manageAdmin", "name": "App\\Controllers\\Admin\\ManageAdminController::manageAdmin", "doc": "&quot;Manage admin accounts page&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageAdminController", "fromLink": "App/Controllers/Admin/ManageAdminController.html", "link": "App/Controllers/Admin/ManageAdminController.html#method_editAdmin", "name": "App\\Controllers\\Admin\\ManageAdminController::editAdmin", "doc": "&quot;Edit admin account page&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageAdminController", "fromLink": "App/Controllers/Admin/ManageAdminController.html", "link": "App/Controllers/Admin/ManageAdminController.html#method_deleteAdmin", "name": "App\\Controllers\\Admin\\ManageAdminController::deleteAdmin", "doc": "&quot;Delete admin account page&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Admin", "fromLink": "App/Controllers/Admin.html", "link": "App/Controllers/Admin/ManageFacultyController.html", "name": "App\\Controllers\\Admin\\ManageFacultyController", "doc": "&quot;Manage faculty controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageFacultyController", "fromLink": "App/Controllers/Admin/ManageFacultyController.html", "link": "App/Controllers/Admin/ManageFacultyController.html#method_manageFaculty", "name": "App\\Controllers\\Admin\\ManageFacultyController::manageFaculty", "doc": "&quot;Manage faculty accounts page&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageFacultyController", "fromLink": "App/Controllers/Admin/ManageFacultyController.html", "link": "App/Controllers/Admin/ManageFacultyController.html#method_editFaculty", "name": "App\\Controllers\\Admin\\ManageFacultyController::editFaculty", "doc": "&quot;Add\/Edit faculty account page&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageFacultyController", "fromLink": "App/Controllers/Admin/ManageFacultyController.html", "link": "App/Controllers/Admin/ManageFacultyController.html#method_deleteFaculty", "name": "App\\Controllers\\Admin\\ManageFacultyController::deleteFaculty", "doc": "&quot;Delete faculty account page&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Admin", "fromLink": "App/Controllers/Admin.html", "link": "App/Controllers/Admin/ManageStudentController.html", "name": "App\\Controllers\\Admin\\ManageStudentController", "doc": "&quot;Admin controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageStudentController", "fromLink": "App/Controllers/Admin/ManageStudentController.html", "link": "App/Controllers/Admin/ManageStudentController.html#method_studentImport", "name": "App\\Controllers\\Admin\\ManageStudentController::studentImport", "doc": "&quot;Student import wizard index&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageStudentController", "fromLink": "App/Controllers/Admin/ManageStudentController.html", "link": "App/Controllers/Admin/ManageStudentController.html#method_studentImport1", "name": "App\\Controllers\\Admin\\ManageStudentController::studentImport1", "doc": "&quot;Student import wizard step 1&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageStudentController", "fromLink": "App/Controllers/Admin/ManageStudentController.html", "link": "App/Controllers/Admin/ManageStudentController.html#method_studentImport2", "name": "App\\Controllers\\Admin\\ManageStudentController::studentImport2", "doc": "&quot;Student import wizard step 2&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageStudentController", "fromLink": "App/Controllers/Admin/ManageStudentController.html", "link": "App/Controllers/Admin/ManageStudentController.html#method_studentImport3", "name": "App\\Controllers\\Admin\\ManageStudentController::studentImport3", "doc": "&quot;Student import wizard step 3&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageStudentController", "fromLink": "App/Controllers/Admin/ManageStudentController.html", "link": "App/Controllers/Admin/ManageStudentController.html#method_studentImport4", "name": "App\\Controllers\\Admin\\ManageStudentController::studentImport4", "doc": "&quot;Student import wizard step 4&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageStudentController", "fromLink": "App/Controllers/Admin/ManageStudentController.html", "link": "App/Controllers/Admin/ManageStudentController.html#method_editStudent", "name": "App\\Controllers\\Admin\\ManageStudentController::editStudent", "doc": "&quot;Add\/Edit student&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Admin\\ManageStudentController", "fromLink": "App/Controllers/Admin/ManageStudentController.html", "link": "App/Controllers/Admin/ManageStudentController.html#method_deleteStudent", "name": "App\\Controllers\\Admin\\ManageStudentController::deleteStudent", "doc": "&quot;Delete student&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Faculty", "fromLink": "App/Controllers/Faculty.html", "link": "App/Controllers/Faculty/GradesController.html", "name": "App\\Controllers\\Faculty\\GradesController", "doc": "&quot;Faculty maion controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Faculty\\GradesController", "fromLink": "App/Controllers/Faculty/GradesController.html", "link": "App/Controllers/Faculty/GradesController.html#method_index", "name": "App\\Controllers\\Faculty\\GradesController::index", "doc": "&quot;Grade import wizard index&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Faculty\\GradesController", "fromLink": "App/Controllers/Faculty/GradesController.html", "link": "App/Controllers/Faculty/GradesController.html#method_import1", "name": "App\\Controllers\\Faculty\\GradesController::import1", "doc": "&quot;Grade import wizard step 1&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Faculty\\GradesController", "fromLink": "App/Controllers/Faculty/GradesController.html", "link": "App/Controllers/Faculty/GradesController.html#method_import2", "name": "App\\Controllers\\Faculty\\GradesController::import2", "doc": "&quot;Grade import wizard step 2&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Faculty\\GradesController", "fromLink": "App/Controllers/Faculty/GradesController.html", "link": "App/Controllers/Faculty/GradesController.html#method_import3", "name": "App\\Controllers\\Faculty\\GradesController::import3", "doc": "&quot;Grade import wizard step 3&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Faculty\\GradesController", "fromLink": "App/Controllers/Faculty/GradesController.html", "link": "App/Controllers/Faculty/GradesController.html#method_import4", "name": "App\\Controllers\\Faculty\\GradesController::import4", "doc": "&quot;Grade import wizard step 4&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Faculty", "fromLink": "App/Controllers/Faculty.html", "link": "App/Controllers/Faculty/MainController.html", "name": "App\\Controllers\\Faculty\\MainController", "doc": "&quot;Faculty maion controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Faculty\\MainController", "fromLink": "App/Controllers/Faculty/MainController.html", "link": "App/Controllers/Faculty/MainController.html#method_index", "name": "App\\Controllers\\Faculty\\MainController::index", "doc": "&quot;Faculty page index&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Faculty", "fromLink": "App/Controllers/Faculty.html", "link": "App/Controllers/Faculty/StudentController.html", "name": "App\\Controllers\\Faculty\\StudentController", "doc": "&quot;Faculty maion controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Faculty\\StudentController", "fromLink": "App/Controllers/Faculty/StudentController.html", "link": "App/Controllers/Faculty/StudentController.html#method_view", "name": "App\\Controllers\\Faculty\\StudentController::view", "doc": "&quot;Faculty student view&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Faculty\\StudentController", "fromLink": "App/Controllers/Faculty/StudentController.html", "link": "App/Controllers/Faculty/StudentController.html#method_edit", "name": "App\\Controllers\\Faculty\\StudentController::edit", "doc": "&quot;Faculty student edit&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers", "fromLink": "App/Controllers.html", "link": "App/Controllers/MainController.html", "name": "App\\Controllers\\MainController", "doc": "&quot;Main controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\MainController", "fromLink": "App/Controllers/MainController.html", "link": "App/Controllers/MainController.html#method_index", "name": "App\\Controllers\\MainController::index", "doc": "&quot;Site root index&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\MainController", "fromLink": "App/Controllers/MainController.html", "link": "App/Controllers/MainController.html#method_login", "name": "App\\Controllers\\MainController::login", "doc": "&quot;Login page&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\MainController", "fromLink": "App/Controllers/MainController.html", "link": "App/Controllers/MainController.html#method_logout", "name": "App\\Controllers\\MainController::logout", "doc": "&quot;Logout page&quot;"},
            
            {"type": "Class", "fromName": "App\\Controllers\\Student", "fromLink": "App/Controllers/Student.html", "link": "App/Controllers/Student/MainController.html", "name": "App\\Controllers\\Student\\MainController", "doc": "&quot;Student controller&quot;"},
                                                        {"type": "Method", "fromName": "App\\Controllers\\Student\\MainController", "fromLink": "App/Controllers/Student/MainController.html", "link": "App/Controllers/Student/MainController.html#method_index", "name": "App\\Controllers\\Student\\MainController::index", "doc": "&quot;Student page index&quot;"},
                    {"type": "Method", "fromName": "App\\Controllers\\Student\\MainController", "fromLink": "App/Controllers/Student/MainController.html", "link": "App/Controllers/Student/MainController.html#method_top", "name": "App\\Controllers\\Student\\MainController::top", "doc": "&quot;Top students page&quot;"},
            
            {"type": "Class", "fromName": "App\\Models", "fromLink": "App/Models.html", "link": "App/Models/Admin.html", "name": "App\\Models\\Admin", "doc": "&quot;Admin model&quot;"},
                                                        {"type": "Method", "fromName": "App\\Models\\Admin", "fromLink": "App/Models/Admin.html", "link": "App/Models/Admin.html#method_setPasswordAttribute", "name": "App\\Models\\Admin::setPasswordAttribute", "doc": "&quot;Auto-hash incoming password&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Admin", "fromLink": "App/Models/Admin.html", "link": "App/Models/Admin.html#method_search", "name": "App\\Models\\Admin::search", "doc": "&quot;Search admin&quot;"},
            
            {"type": "Class", "fromName": "App\\Models", "fromLink": "App/Models.html", "link": "App/Models/Faculty.html", "name": "App\\Models\\Faculty", "doc": "&quot;Faculty model&quot;"},
                                                        {"type": "Method", "fromName": "App\\Models\\Faculty", "fromLink": "App/Models/Faculty.html", "link": "App/Models/Faculty.html#method_setPasswordAttribute", "name": "App\\Models\\Faculty::setPasswordAttribute", "doc": "&quot;Auto-hash incoming password&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Faculty", "fromLink": "App/Models/Faculty.html", "link": "App/Models/Faculty.html#method_search", "name": "App\\Models\\Faculty::search", "doc": "&quot;Search faculty&quot;"},
            
            {"type": "Class", "fromName": "App\\Models", "fromLink": "App/Models.html", "link": "App/Models/Grade.html", "name": "App\\Models\\Grade", "doc": "&quot;Grade model&quot;"},
                                                        {"type": "Method", "fromName": "App\\Models\\Grade", "fromLink": "App/Models/Grade.html", "link": "App/Models/Grade.html#method_student", "name": "App\\Models\\Grade::student", "doc": "&quot;Get student from grade model&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Grade", "fromLink": "App/Models/Grade.html", "link": "App/Models/Grade.html#method_getTopByTermAndSubject", "name": "App\\Models\\Grade::getTopByTermAndSubject", "doc": "&quot;Get top students&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Grade", "fromLink": "App/Models/Grade.html", "link": "App/Models/Grade.html#method_import", "name": "App\\Models\\Grade::import", "doc": "&quot;Import data to database&quot;"},
            
            {"type": "Class", "fromName": "App\\Models", "fromLink": "App/Models.html", "link": "App/Models/Session.html", "name": "App\\Models\\Session", "doc": "&quot;Session model&quot;"},
                    
            {"type": "Class", "fromName": "App\\Models", "fromLink": "App/Models.html", "link": "App/Models/Student.html", "name": "App\\Models\\Student", "doc": "&quot;Student model&quot;"},
                                                        {"type": "Method", "fromName": "App\\Models\\Student", "fromLink": "App/Models/Student.html", "link": "App/Models/Student.html#method_grades", "name": "App\\Models\\Student::grades", "doc": "&quot;Get student grade models&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Student", "fromLink": "App/Models/Student.html", "link": "App/Models/Student.html#method_subjects", "name": "App\\Models\\Student::subjects", "doc": "&quot;Get subjects student has enrolled to&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Student", "fromLink": "App/Models/Student.html", "link": "App/Models/Student.html#method_updateGrades", "name": "App\\Models\\Student::updateGrades", "doc": "&quot;Update student grades&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Student", "fromLink": "App/Models/Student.html", "link": "App/Models/Student.html#method_search", "name": "App\\Models\\Student::search", "doc": "&quot;Search student&quot;"},
                    {"type": "Method", "fromName": "App\\Models\\Student", "fromLink": "App/Models/Student.html", "link": "App/Models/Student.html#method_import", "name": "App\\Models\\Student::import", "doc": "&quot;Import data to database&quot;"},
            
            {"type": "Class", "fromName": "App\\Providers", "fromLink": "App/Providers.html", "link": "App/Providers/IlluminateDatabaseServiceProvider.html", "name": "App\\Providers\\IlluminateDatabaseServiceProvider", "doc": "&quot;Illuminate Database Service&quot;"},
                                                        {"type": "Method", "fromName": "App\\Providers\\IlluminateDatabaseServiceProvider", "fromLink": "App/Providers/IlluminateDatabaseServiceProvider.html", "link": "App/Providers/IlluminateDatabaseServiceProvider.html#method_register", "name": "App\\Providers\\IlluminateDatabaseServiceProvider::register", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Providers\\IlluminateDatabaseServiceProvider", "fromLink": "App/Providers/IlluminateDatabaseServiceProvider.html", "link": "App/Providers/IlluminateDatabaseServiceProvider.html#method_boot", "name": "App\\Providers\\IlluminateDatabaseServiceProvider::boot", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Providers", "fromLink": "App/Providers.html", "link": "App/Providers/TwigExtensionServiceProvider.html", "name": "App\\Providers\\TwigExtensionServiceProvider", "doc": "&quot;Twig extensions&quot;"},
                                                        {"type": "Method", "fromName": "App\\Providers\\TwigExtensionServiceProvider", "fromLink": "App/Providers/TwigExtensionServiceProvider.html", "link": "App/Providers/TwigExtensionServiceProvider.html#method_register", "name": "App\\Providers\\TwigExtensionServiceProvider::register", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Providers\\TwigExtensionServiceProvider", "fromLink": "App/Providers/TwigExtensionServiceProvider.html", "link": "App/Providers/TwigExtensionServiceProvider.html#method_boot", "name": "App\\Providers\\TwigExtensionServiceProvider::boot", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Admin", "fromLink": "App/Routes/Admin.html", "link": "App/Routes/Admin/MainRoute.html", "name": "App\\Routes\\Admin\\MainRoute", "doc": "&quot;Main admin route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Admin\\MainRoute", "fromLink": "App/Routes/Admin/MainRoute.html", "link": "App/Routes/Admin/MainRoute.html#method_connect", "name": "App\\Routes\\Admin\\MainRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Admin", "fromLink": "App/Routes/Admin.html", "link": "App/Routes/Admin/ManageAdminRoute.html", "name": "App\\Routes\\Admin\\ManageAdminRoute", "doc": "&quot;Manage faculty admin route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Admin\\ManageAdminRoute", "fromLink": "App/Routes/Admin/ManageAdminRoute.html", "link": "App/Routes/Admin/ManageAdminRoute.html#method_connect", "name": "App\\Routes\\Admin\\ManageAdminRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Admin", "fromLink": "App/Routes/Admin.html", "link": "App/Routes/Admin/ManageFacultyRoute.html", "name": "App\\Routes\\Admin\\ManageFacultyRoute", "doc": "&quot;Manage faculty admin route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Admin\\ManageFacultyRoute", "fromLink": "App/Routes/Admin/ManageFacultyRoute.html", "link": "App/Routes/Admin/ManageFacultyRoute.html#method_connect", "name": "App\\Routes\\Admin\\ManageFacultyRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Admin", "fromLink": "App/Routes/Admin.html", "link": "App/Routes/Admin/ManageStudentRoute.html", "name": "App\\Routes\\Admin\\ManageStudentRoute", "doc": "&quot;Manage faculty admin route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Admin\\ManageStudentRoute", "fromLink": "App/Routes/Admin/ManageStudentRoute.html", "link": "App/Routes/Admin/ManageStudentRoute.html#method_connect", "name": "App\\Routes\\Admin\\ManageStudentRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Faculty", "fromLink": "App/Routes/Faculty.html", "link": "App/Routes/Faculty/GradesRoute.html", "name": "App\\Routes\\Faculty\\GradesRoute", "doc": "&quot;Faculty route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Faculty\\GradesRoute", "fromLink": "App/Routes/Faculty/GradesRoute.html", "link": "App/Routes/Faculty/GradesRoute.html#method_connect", "name": "App\\Routes\\Faculty\\GradesRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Faculty", "fromLink": "App/Routes/Faculty.html", "link": "App/Routes/Faculty/MainRoute.html", "name": "App\\Routes\\Faculty\\MainRoute", "doc": "&quot;Faculty route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Faculty\\MainRoute", "fromLink": "App/Routes/Faculty/MainRoute.html", "link": "App/Routes/Faculty/MainRoute.html#method_connect", "name": "App\\Routes\\Faculty\\MainRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Faculty", "fromLink": "App/Routes/Faculty.html", "link": "App/Routes/Faculty/StudentRoute.html", "name": "App\\Routes\\Faculty\\StudentRoute", "doc": "&quot;Faculty route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Faculty\\StudentRoute", "fromLink": "App/Routes/Faculty/StudentRoute.html", "link": "App/Routes/Faculty/StudentRoute.html#method_connect", "name": "App\\Routes\\Faculty\\StudentRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes", "fromLink": "App/Routes.html", "link": "App/Routes/MainRoute.html", "name": "App\\Routes\\MainRoute", "doc": "&quot;Main route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\MainRoute", "fromLink": "App/Routes/MainRoute.html", "link": "App/Routes/MainRoute.html#method_connect", "name": "App\\Routes\\MainRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Routes\\Student", "fromLink": "App/Routes/Student.html", "link": "App/Routes/Student/MainRoute.html", "name": "App\\Routes\\Student\\MainRoute", "doc": "&quot;Student route&quot;"},
                                                        {"type": "Method", "fromName": "App\\Routes\\Student\\MainRoute", "fromLink": "App/Routes/Student/MainRoute.html", "link": "App/Routes/Student/MainRoute.html#method_connect", "name": "App\\Routes\\Student\\MainRoute::connect", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/Auth.html", "name": "App\\Services\\Auth", "doc": "&quot;Provides auth service&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Auth", "fromLink": "App/Services/Auth.html", "link": "App/Services/Auth.html#method_attempt", "name": "App\\Services\\Auth::attempt", "doc": "&quot;Authenticate user by username and password&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth", "fromLink": "App/Services/Auth.html", "link": "App/Services/Auth.html#method_user", "name": "App\\Services\\Auth::user", "doc": "&quot;Get logged in user&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth", "fromLink": "App/Services/Auth.html", "link": "App/Services/Auth.html#method_guest", "name": "App\\Services\\Auth::guest", "doc": "&quot;Is user logged in or guest?&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth", "fromLink": "App/Services/Auth.html", "link": "App/Services/Auth.html#method_login", "name": "App\\Services\\Auth::login", "doc": "&quot;Login user&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth", "fromLink": "App/Services/Auth.html", "link": "App/Services/Auth.html#method_logout", "name": "App\\Services\\Auth::logout", "doc": "&quot;Remove session and logout user&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Auth\\Provider", "fromLink": "App/Services/Auth/Provider.html", "link": "App/Services/Auth/Provider/AdminProvider.html", "name": "App\\Services\\Auth\\Provider\\AdminProvider", "doc": "&quot;Admin provider&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AdminProvider", "fromLink": "App/Services/Auth/Provider/AdminProvider.html", "link": "App/Services/Auth/Provider/AdminProvider.html#method_getRole", "name": "App\\Services\\Auth\\Provider\\AdminProvider::getRole", "doc": "&quot;Get provider account role&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AdminProvider", "fromLink": "App/Services/Auth/Provider/AdminProvider.html", "link": "App/Services/Auth/Provider/AdminProvider.html#method_getRedirectRoute", "name": "App\\Services\\Auth\\Provider\\AdminProvider::getRedirectRoute", "doc": "&quot;Get route to redirect after login&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AdminProvider", "fromLink": "App/Services/Auth/Provider/AdminProvider.html", "link": "App/Services/Auth/Provider/AdminProvider.html#method_getAllowedControllers", "name": "App\\Services\\Auth\\Provider\\AdminProvider::getAllowedControllers", "doc": "&quot;Get protected route group the user has access to&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AdminProvider", "fromLink": "App/Services/Auth/Provider/AdminProvider.html", "link": "App/Services/Auth/Provider/AdminProvider.html#method_getName", "name": "App\\Services\\Auth\\Provider\\AdminProvider::getName", "doc": "&quot;Get user name&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AdminProvider", "fromLink": "App/Services/Auth/Provider/AdminProvider.html", "link": "App/Services/Auth/Provider/AdminProvider.html#method_attempt", "name": "App\\Services\\Auth\\Provider\\AdminProvider::attempt", "doc": "&quot;Attempt to login&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Auth\\Provider", "fromLink": "App/Services/Auth/Provider.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "doc": "&quot;Interface for auth providers&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getRole", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getRole", "doc": "&quot;Get provider account role&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getRedirectRoute", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getRedirectRoute", "doc": "&quot;Get route to redirect after login&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getAllowedControllers", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getAllowedControllers", "doc": "&quot;Get protected route group the user has access to&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_getName", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::getName", "doc": "&quot;Get user name&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\AuthProviderInterface", "fromLink": "App/Services/Auth/Provider/AuthProviderInterface.html", "link": "App/Services/Auth/Provider/AuthProviderInterface.html#method_attempt", "name": "App\\Services\\Auth\\Provider\\AuthProviderInterface::attempt", "doc": "&quot;Attempt to login&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Auth\\Provider", "fromLink": "App/Services/Auth/Provider.html", "link": "App/Services/Auth/Provider/FacultyProvider.html", "name": "App\\Services\\Auth\\Provider\\FacultyProvider", "doc": "&quot;Faculty provider&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\FacultyProvider", "fromLink": "App/Services/Auth/Provider/FacultyProvider.html", "link": "App/Services/Auth/Provider/FacultyProvider.html#method_getRole", "name": "App\\Services\\Auth\\Provider\\FacultyProvider::getRole", "doc": "&quot;Get provider account role&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\FacultyProvider", "fromLink": "App/Services/Auth/Provider/FacultyProvider.html", "link": "App/Services/Auth/Provider/FacultyProvider.html#method_getRedirectRoute", "name": "App\\Services\\Auth\\Provider\\FacultyProvider::getRedirectRoute", "doc": "&quot;Get route to redirect after login&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\FacultyProvider", "fromLink": "App/Services/Auth/Provider/FacultyProvider.html", "link": "App/Services/Auth/Provider/FacultyProvider.html#method_getAllowedControllers", "name": "App\\Services\\Auth\\Provider\\FacultyProvider::getAllowedControllers", "doc": "&quot;Get protected route group the user has access to&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\FacultyProvider", "fromLink": "App/Services/Auth/Provider/FacultyProvider.html", "link": "App/Services/Auth/Provider/FacultyProvider.html#method_getName", "name": "App\\Services\\Auth\\Provider\\FacultyProvider::getName", "doc": "&quot;Get user name&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\FacultyProvider", "fromLink": "App/Services/Auth/Provider/FacultyProvider.html", "link": "App/Services/Auth/Provider/FacultyProvider.html#method_attempt", "name": "App\\Services\\Auth\\Provider\\FacultyProvider::attempt", "doc": "&quot;Attempt to login&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Auth\\Provider", "fromLink": "App/Services/Auth/Provider.html", "link": "App/Services/Auth/Provider/StudentProvider.html", "name": "App\\Services\\Auth\\Provider\\StudentProvider", "doc": "&quot;Student provider&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\StudentProvider", "fromLink": "App/Services/Auth/Provider/StudentProvider.html", "link": "App/Services/Auth/Provider/StudentProvider.html#method_getRole", "name": "App\\Services\\Auth\\Provider\\StudentProvider::getRole", "doc": "&quot;Get provider account role&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\StudentProvider", "fromLink": "App/Services/Auth/Provider/StudentProvider.html", "link": "App/Services/Auth/Provider/StudentProvider.html#method_getRedirectRoute", "name": "App\\Services\\Auth\\Provider\\StudentProvider::getRedirectRoute", "doc": "&quot;Get route to redirect after login&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\StudentProvider", "fromLink": "App/Services/Auth/Provider/StudentProvider.html", "link": "App/Services/Auth/Provider/StudentProvider.html#method_getAllowedControllers", "name": "App\\Services\\Auth\\Provider\\StudentProvider::getAllowedControllers", "doc": "&quot;Get protected route group the user has access to&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\StudentProvider", "fromLink": "App/Services/Auth/Provider/StudentProvider.html", "link": "App/Services/Auth/Provider/StudentProvider.html#method_getName", "name": "App\\Services\\Auth\\Provider\\StudentProvider::getName", "doc": "&quot;Get user name&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\Provider\\StudentProvider", "fromLink": "App/Services/Auth/Provider/StudentProvider.html", "link": "App/Services/Auth/Provider/StudentProvider.html#method_attempt", "name": "App\\Services\\Auth\\Provider\\StudentProvider::attempt", "doc": "&quot;Attempt to login&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Auth", "fromLink": "App/Services/Auth.html", "link": "App/Services/Auth/User.html", "name": "App\\Services\\Auth\\User", "doc": "&quot;Generic user class&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Auth\\User", "fromLink": "App/Services/Auth/User.html", "link": "App/Services/Auth/User.html#method_createFromSerializedData", "name": "App\\Services\\Auth\\User::createFromSerializedData", "doc": "&quot;Create User object from serialized data. Used for saved sessions&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\User", "fromLink": "App/Services/Auth/User.html", "link": "App/Services/Auth/User.html#method___construct", "name": "App\\Services\\Auth\\User::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\User", "fromLink": "App/Services/Auth/User.html", "link": "App/Services/Auth/User.html#method_getModel", "name": "App\\Services\\Auth\\User::getModel", "doc": "&quot;Get user model&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\User", "fromLink": "App/Services/Auth/User.html", "link": "App/Services/Auth/User.html#method_getProvider", "name": "App\\Services\\Auth\\User::getProvider", "doc": "&quot;Get user provider&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\User", "fromLink": "App/Services/Auth/User.html", "link": "App/Services/Auth/User.html#method_getName", "name": "App\\Services\\Auth\\User::getName", "doc": "&quot;Get user name&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\User", "fromLink": "App/Services/Auth/User.html", "link": "App/Services/Auth/User.html#method_getRole", "name": "App\\Services\\Auth\\User::getRole", "doc": "&quot;Get user role&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Auth\\User", "fromLink": "App/Services/Auth/User.html", "link": "App/Services/Auth/User.html#method_serialize", "name": "App\\Services\\Auth\\User::serialize", "doc": "&quot;Get serialized user data&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/Csrf.html", "name": "App\\Services\\Csrf", "doc": "&quot;Provides static class for CSRF from CSRF service&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Csrf", "fromLink": "App/Services/Csrf.html", "link": "App/Services/Csrf.html#method_generate", "name": "App\\Services\\Csrf::generate", "doc": "&quot;Generate CSRF token&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Csrf", "fromLink": "App/Services/Csrf.html", "link": "App/Services/Csrf.html#method_isValid", "name": "App\\Services\\Csrf::isValid", "doc": "&quot;Check if token is valid for the given identifier&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/Form.html", "name": "App\\Services\\Form", "doc": "&quot;Provides wrapper and factory for form service&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Form", "fromLink": "App/Services/Form.html", "link": "App/Services/Form.html#method_create", "name": "App\\Services\\Form::create", "doc": "&quot;Create a new form builder&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Form", "fromLink": "App/Services/Form.html", "link": "App/Services/Form.html#method_flashError", "name": "App\\Services\\Form::flashError", "doc": "&quot;Add flash error message to form&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Form", "fromLink": "App/Services/Form.html", "link": "App/Services/Form.html#method_handleFlashErrors", "name": "App\\Services\\Form::handleFlashErrors", "doc": "&quot;Apply flashed errors to form&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/GradingSheet.html", "name": "App\\Services\\GradingSheet", "doc": "&quot;Grading Sheet&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\GradingSheet", "fromLink": "App/Services/GradingSheet.html", "link": "App/Services/GradingSheet.html#method___construct", "name": "App\\Services\\GradingSheet::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\GradingSheet", "fromLink": "App/Services/GradingSheet.html", "link": "App/Services/GradingSheet.html#method_getSheets", "name": "App\\Services\\GradingSheet::getSheets", "doc": "&quot;Get sheets&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\GradingSheet", "fromLink": "App/Services/GradingSheet.html", "link": "App/Services/GradingSheet.html#method_getSheetContents", "name": "App\\Services\\GradingSheet::getSheetContents", "doc": "&quot;Get sheet contents&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\GradingSheet", "fromLink": "App/Services/GradingSheet.html", "link": "App/Services/GradingSheet.html#method_getSheetsContents", "name": "App\\Services\\GradingSheet::getSheetsContents", "doc": "&quot;Get sheets contents.&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/Hash.html", "name": "App\\Services\\Hash", "doc": "&quot;Provides hash service&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Hash", "fromLink": "App/Services/Hash.html", "link": "App/Services/Hash.html#method_make", "name": "App\\Services\\Hash::make", "doc": "&quot;Creates a password hash&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Hash", "fromLink": "App/Services/Hash.html", "link": "App/Services/Hash.html#method_check", "name": "App\\Services\\Hash::check", "doc": "&quot;Verifies that the given hash matches the given password&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Hash", "fromLink": "App/Services/Hash.html", "link": "App/Services/Hash.html#method_needsRehash", "name": "App\\Services\\Hash::needsRehash", "doc": "&quot;Check if password needs rehash&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/Helper.html", "name": "App\\Services\\Helper", "doc": "&quot;Helper class&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Helper", "fromLink": "App/Services/Helper.html", "link": "App/Services/Helper.html#method_parseId", "name": "App\\Services\\Helper::parseId", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Helper", "fromLink": "App/Services/Helper.html", "link": "App/Services/Helper.html#method_isStudentId", "name": "App\\Services\\Helper::isStudentId", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Helper", "fromLink": "App/Services/Helper.html", "link": "App/Services/Helper.html#method_formatStudentId", "name": "App\\Services\\Helper::formatStudentId", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Helper", "fromLink": "App/Services/Helper.html", "link": "App/Services/Helper.html#method_formatGrade", "name": "App\\Services\\Helper::formatGrade", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Helper", "fromLink": "App/Services/Helper.html", "link": "App/Services/Helper.html#method_getGradeClass", "name": "App\\Services\\Helper::getGradeClass", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/OmegaSheet.html", "name": "App\\Services\\OmegaSheet", "doc": "&quot;Omega Sheet&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\OmegaSheet", "fromLink": "App/Services/OmegaSheet.html", "link": "App/Services/OmegaSheet.html#method___construct", "name": "App\\Services\\OmegaSheet::__construct", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\OmegaSheet", "fromLink": "App/Services/OmegaSheet.html", "link": "App/Services/OmegaSheet.html#method_getSheets", "name": "App\\Services\\OmegaSheet::getSheets", "doc": "&quot;Get sheets&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\OmegaSheet", "fromLink": "App/Services/OmegaSheet.html", "link": "App/Services/OmegaSheet.html#method_getSheetContents", "name": "App\\Services\\OmegaSheet::getSheetContents", "doc": "&quot;Get sheet contents&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\OmegaSheet", "fromLink": "App/Services/OmegaSheet.html", "link": "App/Services/OmegaSheet.html#method_getSheetsContents", "name": "App\\Services\\OmegaSheet::getSheetsContents", "doc": "&quot;Get sheets contents.&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/Service.html", "name": "App\\Services\\Service", "doc": "&quot;Base service class&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Service", "fromLink": "App/Services/Service.html", "link": "App/Services/Service.html#method_setApplication", "name": "App\\Services\\Service::setApplication", "doc": "&quot;Set application container&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Session", "fromLink": "App/Services/Session.html", "link": "App/Services/Session/EloquentSessionHandler.html", "name": "App\\Services\\Session\\EloquentSessionHandler", "doc": "&quot;Provides session handler for database&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Session\\EloquentSessionHandler", "fromLink": "App/Services/Session/EloquentSessionHandler.html", "link": "App/Services/Session/EloquentSessionHandler.html#method_open", "name": "App\\Services\\Session\\EloquentSessionHandler::open", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Session\\EloquentSessionHandler", "fromLink": "App/Services/Session/EloquentSessionHandler.html", "link": "App/Services/Session/EloquentSessionHandler.html#method_close", "name": "App\\Services\\Session\\EloquentSessionHandler::close", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Session\\EloquentSessionHandler", "fromLink": "App/Services/Session/EloquentSessionHandler.html", "link": "App/Services/Session/EloquentSessionHandler.html#method_read", "name": "App\\Services\\Session\\EloquentSessionHandler::read", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Session\\EloquentSessionHandler", "fromLink": "App/Services/Session/EloquentSessionHandler.html", "link": "App/Services/Session/EloquentSessionHandler.html#method_write", "name": "App\\Services\\Session\\EloquentSessionHandler::write", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Session\\EloquentSessionHandler", "fromLink": "App/Services/Session/EloquentSessionHandler.html", "link": "App/Services/Session/EloquentSessionHandler.html#method_gc", "name": "App\\Services\\Session\\EloquentSessionHandler::gc", "doc": "&quot;&quot;"},
                    {"type": "Method", "fromName": "App\\Services\\Session\\EloquentSessionHandler", "fromLink": "App/Services/Session/EloquentSessionHandler.html", "link": "App/Services/Session/EloquentSessionHandler.html#method_destroy", "name": "App\\Services\\Session\\EloquentSessionHandler::destroy", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Session", "fromLink": "App/Services/Session.html", "link": "App/Services/Session/FlashBag.html", "name": "App\\Services\\Session\\FlashBag", "doc": "&quot;Static class wrapper for session flashbag&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Session\\FlashBag", "fromLink": "App/Services/Session/FlashBag.html", "link": "App/Services/Session/FlashBag.html#method___callStatic", "name": "App\\Services\\Session\\FlashBag::__callStatic", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Services\\Session", "fromLink": "App/Services/Session.html", "link": "App/Services/Session/Session.html", "name": "App\\Services\\Session\\Session", "doc": "&quot;Static class wrapper for session service&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\Session\\Session", "fromLink": "App/Services/Session/Session.html", "link": "App/Services/Session/Session.html#method___callStatic", "name": "App\\Services\\Session\\Session::__callStatic", "doc": "&quot;&quot;"},
            
            {"type": "Class", "fromName": "App\\Services", "fromLink": "App/Services.html", "link": "App/Services/View.html", "name": "App\\Services\\View", "doc": "&quot;View renderer wrapper for Twig&quot;"},
                                                        {"type": "Method", "fromName": "App\\Services\\View", "fromLink": "App/Services/View.html", "link": "App/Services/View.html#method_render", "name": "App\\Services\\View::render", "doc": "&quot;Render view&quot;"},
            
            
                                        // Fix trailing commas in the index
        {}
    ];

    /** Tokenizes strings by namespaces and functions */
    function tokenizer(term) {
        if (!term) {
            return [];
        }

        var tokens = [term];
        var meth = term.indexOf('::');

        // Split tokens into methods if "::" is found.
        if (meth > -1) {
            tokens.push(term.substr(meth + 2));
            term = term.substr(0, meth - 2);
        }

        // Split by namespace or fake namespace.
        if (term.indexOf('\\') > -1) {
            tokens = tokens.concat(term.split('\\'));
        } else if (term.indexOf('_') > 0) {
            tokens = tokens.concat(term.split('_'));
        }

        // Merge in splitting the string by case and return
        tokens = tokens.concat(term.match(/(([A-Z]?[^A-Z]*)|([a-z]?[^a-z]*))/g).slice(0,-1));

        return tokens;
    };

    root.Sami = {
        /**
         * Cleans the provided term. If no term is provided, then one is
         * grabbed from the query string "search" parameter.
         */
        cleanSearchTerm: function(term) {
            // Grab from the query string
            if (typeof term === 'undefined') {
                var name = 'search';
                var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
                var results = regex.exec(location.search);
                if (results === null) {
                    return null;
                }
                term = decodeURIComponent(results[1].replace(/\+/g, " "));
            }

            return term.replace(/<(?:.|\n)*?>/gm, '');
        },

        /** Searches through the index for a given term */
        search: function(term) {
            // Create a new search index if needed
            if (!bhIndex) {
                bhIndex = new Bloodhound({
                    limit: 500,
                    local: searchIndex,
                    datumTokenizer: function (d) {
                        return tokenizer(d.name);
                    },
                    queryTokenizer: Bloodhound.tokenizers.whitespace
                });
                bhIndex.initialize();
            }

            results = [];
            bhIndex.get(term, function(matches) {
                results = matches;
            });

            if (!rootPath) {
                return results;
            }

            // Fix the element links based on the current page depth.
            return $.map(results, function(ele) {
                if (ele.link.indexOf('..') > -1) {
                    return ele;
                }
                ele.link = rootPath + ele.link;
                if (ele.fromLink) {
                    ele.fromLink = rootPath + ele.fromLink;
                }
                return ele;
            });
        },

        /** Get a search class for a specific type */
        getSearchClass: function(type) {
            return searchTypeClasses[type] || searchTypeClasses['_'];
        },

        /** Add the left-nav tree to the site */
        injectApiTree: function(ele) {
            ele.html(treeHtml);
        }
    };

    $(function() {
        // Modify the HTML to work correctly based on the current depth
        rootPath = $('body').attr('data-root-path');
        treeHtml = treeHtml.replace(/href="/g, 'href="' + rootPath);
        Sami.injectApiTree($('#api-tree'));
    });

    return root.Sami;
})(window);

$(function() {

    // Enable the version switcher
    $('#version-switcher').change(function() {
        window.location = $(this).val()
    });

    
        // Toggle left-nav divs on click
        $('#api-tree .hd span').click(function() {
            $(this).parent().parent().toggleClass('opened');
        });

        // Expand the parent namespaces of the current page.
        var expected = $('body').attr('data-name');

        if (expected) {
            // Open the currently selected node and its parents.
            var container = $('#api-tree');
            var node = $('#api-tree li[data-name="' + expected + '"]');
            // Node might not be found when simulating namespaces
            if (node.length > 0) {
                node.addClass('active').addClass('opened');
                node.parents('li').addClass('opened');
                var scrollPos = node.offset().top - container.offset().top + container.scrollTop();
                // Position the item nearer to the top of the screen.
                scrollPos -= 200;
                container.scrollTop(scrollPos);
            }
        }

    
    
        var form = $('#search-form .typeahead');
        form.typeahead({
            hint: true,
            highlight: true,
            minLength: 1
        }, {
            name: 'search',
            displayKey: 'name',
            source: function (q, cb) {
                cb(Sami.search(q));
            }
        });

        // The selection is direct-linked when the user selects a suggestion.
        form.on('typeahead:selected', function(e, suggestion) {
            window.location = suggestion.link;
        });

        // The form is submitted when the user hits enter.
        form.keypress(function (e) {
            if (e.which == 13) {
                $('#search-form').submit();
                return true;
            }
        });

    
});


