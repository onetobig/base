# 后端接口基础框架
1、安装拓展
> composer install

2、复制配置文件 .env，修改其中数据库配置
>copy .env.example .env

3、生成授权服务
```shell
php artisan passport:client --personal

#What should we name the personal access client? [Laravel Personal Access Client]:
# 输入入名称
api

# 结果
# Personal access client created successfully.
Client ID: 1
Client secret: GWWyAOnglHpCvYxxxxxxxxxxxxxxxxxxxxxxxx
```

修改配置文件 .env 对应的值
```shell
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=GWWyAOnglHpCvYxxxxxxxxxxxxxxxxxxxxxxxx
```
