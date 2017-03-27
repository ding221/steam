###**Steam Bot**

##### **如何使用**

  - 根目录下执行composer install
  - 配置「conf」目录下的 「cfg.csv」文件.如果使用手机令牌，需要手动在 User.php 文件中修改 doLogin 方法 $data 数组的「twofactorcode」值，为手机令牌值
  - 运行

       ```
        php start_web.php
       ```
