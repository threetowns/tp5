## 数据库设计

* ETH数量，单位为 ：`decimal(27.10)`



## 一、业务逻辑

### 1. 注册

#### 1.1  用户名 + 密码 （+ 密码提示），新建用户

- password规则： `md5('hello' . md5($password) . 'imtoken');`
- unique_key规则： `md5($data['username'] . $data['password']) . md5(md5(time() . 'imtoken'));`
- 前端 `localStorage` 存储 `username`和 `unique_key`

> - 请求接口：`/api/user/create`
>
> - 请求参数：
>
>   ```json
>   {
>       "username": "leon",
>       "password": "123456"
>   }
>   ```

#### 1.2 创建 **助记词**

> 助记词成功后，生成 BTC和ETH，一并更新记录
>
> - ETH地址：`0xe7ca5aEf09cb8AF943ae0A107953b1EaA3f95047`，状态：1
> - ETH数量：0

### 2.首页

#### 2.1 获取币总

> 利用 `token` 存在用户信息，用之前的 `unique_key` 作为token.
>
> - token生成原理：`md5(time())`  ，这样每次就不一样
>
>   - 如果前端没有传 token过来，则跳转到 **登录页**，因为登录的时候会把用户信息 `find()` 一次
>
>   - 缓存用的是`cache()`，规则如下：
>
>     `cache($cache_name, $cache_value, 3600)`
>
>     - $cache_name，为每次过期跳到登录时生成，如： md5(time())
>     - $cache_value，为登录时查找用户信息的结果集，全部保存啦。
>     - 3600，它是一个过期时间： 60 * 60，即有效期为 1小时
>
> - token更新
>
>   `cache($cache_name, $cache_name, 7200)`

------

> 多表联查：从wallet_type中关联 图标（图片地址）、 类型（如1 =>ETH，2=>BTC）
>
> - wallet_type表设计字段参考： https://token-profile.token.im/token/ETHEREUM?locale=zh-CN
> - https://api.coinmarketcap.com/v2/ticker/1027/ 



### 2.2 首页逻辑

> 1. 获取钱包类型：`api/wallet/list`，参数：`status=1&pid=0 `。
>    * 本地存储： `localStorage.setItem('wallet', data)`，current当前钱包类型和type钱包类型
> 2. 获取当前用户钱包信息：`/api/wallet/index`
>    * 类型为：current的当前用户钱包，且status为 1（表示正在使用的）
> 3. 管理资产：目前只有ETH有些功能
>    * 我的资产：**多表联查**，前端使用 `vuex`状态管理
>      * 没记录，则新增至 `wallet` 表，状态为 `1`；
>      * 取消，则状态为 `0`；
>      * 有记录，修改状态 `0` 或 `1`
> 4. 



## 二、API文档

### 1. 注册





### 2. 获取资产

#### 2.1 资产分类

* 请求方式：`POST|GET`

* 请求地址：`/api/wallet/type`

* 请求参数：

  无（后台会查询`pid` 为 `0 ` 的钱包）




#### 2.2 资产列表

* 请求方式：`POST`

* 请求地址：`/api/wallet/list`

* 模糊查询： TODO：验证指定字段类型

* 请求参数：

  ```json
  {
      "keyword": '',
      "pid": '',
      "status": ''
  }
  ```

  



#### 2.3 获取当前用户资产

* 请求方式：`POST`

* 请求地址：`/api/wallet/index`

* 请求参数：

  ```json
  {
      "user_token": "61d0fc4467b7fe282f6ebfff6d32cbd0c59c892328dc98b978d66dd0875fd085",
  	"wallet_type": 1,  // 钱包类型，取 `/api/wallet/type` 里的结果
      "status": 1 // 钱包状态，默认是`/api/wallet/list`里状态为1
  }
  ```

* 返回结果：

  ```json
  {
  "state": 1,
  "data": [{
      "name": "ETH-Wallet",                                          // 钱包名字
      "type": 1,                                                     // 对应 wallet_type里 wid
      "status": 1,                                                   
      "num": 10,                                                     // 数量
      "address": "0xQRZoqr701J67NFQta5EiWSrN9gMrGjcxzfm7qc7K",       // 合约地址
      "pid": 0,
      "logo_icon": "https://token-profile.token.im/ethereum.png",    // 币logo图标
      "ticker_id": 1027,                                             // ticker id
      "t_symbol": "ETH"                                              // 简写符号
  },{
      "name": null,
      "type": 3,
      "status": 1,
      "num": 5,
      "address": "0xQRZoqr701J67NFQta5EiWSrN9gdddddddm7qc7K",
      "pid": 1,
      "logo_icon": "https://token-profile.token.im/0x5CA9a71B1d01849C0a95490Cc00559717fCF0D1d.png",
      "ticker_id": 1700,
      "t_symbol": "AE"
  }],
  "message": "操作成功"
  }
  ```

  

#### 2.4 更新资产状态

* 请求方式：`POST`

* 请求地址：`/api/wallet/update`

* 请求参数：

  ```json
  {
      "user_token": "....",
      "walletIds" [1,2]  // 钱包ID数组
  }
  ```

  



## 三、后台接口

### 3.1 登录

* 请求地址：`/admin/index/login`

* 请求方式：`POST`

* 请求参数：

  ```json
  {
      "username": '',
      "password": ''
  }
  ```

* 返回参数：

  ```json
  {
      "state": 1,
      "data": {
          "token": "36a0a775b9fd762134702511a6093219",
          "username": "admin"
      },
      "message": "登录成功"
  }
  ```



### 3.2 订单（含分页）

* 请求地址：`/admin/index/order`

* 请求方式：`POST`

* 请求参数：

  ```json
  {
      page: 1,
      rows: 10,
      address: '' // 收款或付款地址，非必须
      hash: ''    // 交易hash
      type: 1     // 订单类型（1. eth; 2. btc; 3. AE ...）
  }
  ```



### 3.3 钱包

#### 3.3.1 列表

* 请求地址：`/admin/index/wallet`

* 请求方式：`POST`

* 请求参数：

  ```json
  {
      page: 1,
      rows: 10,
      address: ''   // 钱包地址，非必须
      type: 1,      // 类型
      username: ''  // 用户名（联表查询）
  }
  ```

#### 3.3.2 修改

* 请求地址：`/admin/index/wallet_edit/`

* 请求方式：`POST`

* 请求参数：

  ```json
  {
      id: '',
      num: ''
  }
  ```



### 3.4 币种

#### 3.4.1 列表 

* 请求地址：`/admin/index/currency`

* 请求方式：`POST`

* 请求参数：

  ```json
  {
      page: 1,
      rows: 10
  }
  ```

  

#### 3.4.2 编辑（currency_edit）

#### 3.4.3 增加（current_add）

#### 3.4.4 删除（current_del）



