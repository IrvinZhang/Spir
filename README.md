##Spir是什么?
简单的说就是一个适用于`PHP7.X的MVCS框架`
##构思源自于：
* [CodeIgniter](https://www.codeigniter.com/)
* [Smarty](http://www.smarty.net/)
* [FIS3](http://fis.baidu.com/)

##Spir架构图：
![spir](http://7xnomm.com1.z0.glb.clouddn.com/Spir.png)
##Spir有哪些功能？

* Spir采用`MVCS`的模式：
    *  Spir在传统的MVC（Model-View-Controller）模式下，引入了Service（服务）层
    *  Service（服务）层的使用可以参考上图：
        *   Service层是什么？`Service是服务层`（在MVCS模式下，可以理解为一个功能即是一个服务，例如：注册服务，登录服务、验证服务等）
        *   Service层大大降低了Controller与Model层的耦合性，使得逻辑结构更加清晰，方便开发与维护，并且Service层使得自身与Model层的功能与代码都具有了`非常好的可重用（移植）性`
        *   Service层按功能进行分类，例如：为验证码功能编写一个Service，该Service里面就可能含有产生验证码、将验证码存入缓存、通过短信发送验证码、通过邮件发送验证码等相关功能
    *   在MVCS模式中，Controller相当于一个路由的作用，将前端请求路由到各个需要Service上。需要注意一点的是，抽象出的Service层，甚至可以是外部服务，例如：可以用其它编程语言编写一个`外部Service`，再通过Controller路由过去，这就体现出MVCS模式的另一个优点：`扩展性极佳`
    *   在MVCS模式中，Model相当于一个数据表的抽象（或者说一个集合的抽象），使得一个Model只管理与其对应的一张表，逻辑结构十分简单明了
* 后端：
    * Spir继承了CodeIgniter的优点，并且强制用户使用PHP7.X，只为着重一点：`不让编程语言成为性能的瓶颈`
    * Spir集成了`最新版Smarty`，并加入了许多方便的自定义语法，使用模板开发，简单明了
    * Spir集成了`安全处理、缓存机制、Composer、Mysql、Memcached、Redis、Curl`等常用模块，方便开发
    * 使用Spir可以很方便的使用自动化部署
    * 若自带的模块不能满足您的需要，还可以很方便的通过Composer增加项目所需模块
* 前端：
    * Spir集成了百度比较完善的解决方案：`FIS3`
    * 静态资源整合
    * 模块化开发
    * 前后端分离、一键部署
    * Less、Sass、CoffeeScript等大量相关扩展的解决方案

##快速使用：
```
1. 安装 `Node.js[必须]` `PHP7[必须]` `Composer[必须]`
2. `$ npm install -g fis3
3. `$ npm install -g fis3-smarty
4. `$ git clone https://github.com/IrvinZhang/Spir.git
5. `$ cd Spir/
6. `$ fis3 release -wd /var/www/html/www.spir.com/  （后面为项目部署的路径，需要自己做修改【也可以使用默认的路径】）
7. `$ cd Spir/application/views/templates
8. `$ vim fis-conf.js  （将“/var/www/html/www.spir.com/” 改为您上面所设置的地址，如果上面没改，这里也不需要更改）
9. `$ fis3 release -wL
10. `$ cd Spir/application/
11. `$ composer update -vvv
12. 配置web服务器与本地hosts文件，解析该目录（若没修改，则为：“/var/www/html/www.spir.com/”）
13. 访问站点测试是否成功
```

##有问题反馈
在使用中有任何问题，欢迎反馈给我，可以用以下联系方式跟我交流

* Email：287305501@qq.com
* QQ: 287305501
* weibo: [@Geek-Irvin](http://www.weibo.com/cqzhangwen)

