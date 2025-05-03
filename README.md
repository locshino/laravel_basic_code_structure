# **Laravel Basic Code Structure**

## **Giới thiệu**

Ứng dụng này được xây dựng bằng Laravel nhằm mục đích minh họa và cung cấp một cấu trúc code cơ bản, rõ ràng cho dự án web, đặc biệt phù hợp cho các nhóm mới bắt đầu làm quen với kiến trúc phân lớp. Ứng dụng này kết hợp mô hình MVC truyền thống của Laravel với các tầng **Service** và **Repository** để phân tách rõ ràng trách nhiệm của từng phần trong ứng dụng.

Mục tiêu chính là giúp các thành viên trong nhóm hiểu được luồng xử lý của một yêu cầu (request) đi qua các tầng khác nhau như thế nào, cách tổ chức logic nghiệp vụ và thao tác dữ liệu một cách hiệu quả, dễ bảo trì và mở rộng.

## **Tính năng cơ bản**

Ứng dụng cung cấp các tính năng quản lý cơ bản cho một hệ thống thương mại điện tử nhỏ:

* **Quản lý Sản phẩm:** Xem danh sách sản phẩm (bao gồm hình ảnh), xem chi tiết, thêm mới, chỉnh sửa, xóa sản phẩm.  
* **Quản lý Thể loại:** Xem danh sách thể loại, thêm mới, chỉnh sửa, xóa thể loại.  
* **Quản lý Người dùng:** Xem danh sách người dùng, xem chi tiết, thêm mới, chỉnh sửa, xóa người dùng.  
* **Phân quyền Người dùng:** Hệ thống vai trò (Roles) và quyền hạn (Permissions) cơ bản với 3 vai trò chính:  
  * **Khách (Guest):** Chỉ có thể xem danh sách sản phẩm (các sản phẩm đang hoạt động).  
  * **Quản lý (Manager):** Có thể quản lý (thêm, sửa, xóa) sản phẩm và thể loại. Có thể xem tất cả sản phẩm (bao gồm không hoạt động).  
  * **Admin:** Có toàn quyền truy cập vào tất cả các tính năng quản lý (sản phẩm, thể loại, người dùng) và phân quyền người dùng.

## **Cấu trúc Code**

Ứng dụng tuân theo kiến trúc **MVC mở rộng** với các tầng **Service** và **Repository**, cùng với việc sử dụng **Form Request** cho validation và **Custom Exceptions** cho xử lý lỗi nghiệp vụ.

* **Controller (app/Http/Controllers):**  
  * Nhiệm vụ: Xử lý request HTTP, gọi các phương thức từ tầng Service, bắt các Exception từ tầng Service/Repository và trả về Response (View cho Web, JSON cho API).  
  * Đặc điểm: Nên "mỏng" (thin), chỉ chứa logic điều phối và xử lý giao tiếp với bên ngoài (request/response).  
* **Service (app/Services):**  
  * Nhiệm vụ: Chứa toàn bộ **logic nghiệp vụ (Business Logic)** phức tạp.  
  * Đặc điểm: Nhận dữ liệu đã được validate, thực hiện các quy tắc kinh doanh, gọi các phương thức từ một hoặc nhiều Repository, tương tác với các dịch vụ khác (nếu có), ném ra Custom Exception khi có lỗi nghiệp vụ.  
* **Repository (app/Repositories):**  
  * Nhiệm vụ: Trừu tượng hóa việc **truy cập dữ liệu (Data Access)**.  
  * Đặc điểm: Cung cấp các phương thức CRUD và truy vấn dữ liệu, làm việc trực tiếp với Model (hoặc DB), trả về Model/Collection, ném ra Exception liên quan đến dữ liệu (ví dụ: Not Found). Được định nghĩa bằng **Interfaces** (app/Interfaces/Repositories) và cài đặt bằng các lớp cụ thể (ví dụ: app/Repositories/Eloquent).  
* **Model (app/Models):**  
  * Nhiệm vụ: Đại diện cho cấu trúc dữ liệu và tương tác trực tiếp với cơ sở dữ liệu (sử dụng Eloquent ORM).  
  * Đặc điểm: Định nghĩa các trường, quan hệ (relationships), scope, accessor/mutator. Không chứa logic nghiệp vụ phức tạp.  
* **View (resources/views):**  
  * Nhiệm vụ: Hiển thị giao diện người dùng.  
  * Đặc điểm: Sử dụng Blade template, nhận dữ liệu từ Controller. Sử dụng **Blade UI Kit** và **DaisyUI** để xây dựng giao diện nhanh chóng và đẹp mắt.  
* **Form Request (app/Http/Requests):**  
  * Nhiệm vụ: Xử lý **validation** dữ liệu đầu vào và **authorization** cơ bản trước khi request đến Controller.  
  * Đặc điểm: Giúp Controller gọn gàng hơn, tập trung logic validation vào một nơi.  
* **Exception (app/Exceptions):**  
  * Nhiệm vụ: Biểu diễn các tình huống lỗi cụ thể (ví dụ: ProductNotFoundException, PermissionDeniedException).  
  * Đặc điểm: Được ném ra từ tầng Service/Repository và bắt ở tầng Controller hoặc Exception Handler toàn cục.  
* **Interface (app/Interfaces):**  
  * Nhiệm vụ: Định nghĩa "hợp đồng" cho các lớp Service và Repository.  
  * Đặc điểm: Giúp các tầng phụ thuộc vào abstraction thay vì implementation cụ thể, tăng tính linh hoạt và khả năng kiểm thử (sử dụng Dependency Injection trong Service Provider).  
* **Authorization (Spatie Laravel Permission, Gates, Policies):**  
  * Nhiệm vụ: Kiểm tra xem người dùng có được phép thực hiện hành động hay không.  
  * Đặc điểm: Sử dụng package Spatie để quản lý Roles/Permissions, kết hợp với Laravel Gates và Policies để kiểm tra quyền tại Controller, Form Request và Views.

## **Cấu trúc thư mục chính (app/)**

app/  
├── Console/  
├── Exceptions/             // Custom Exception classes  
├── Http/  
│   ├── Controllers/  
│   │   ├── Api/            // API Controllers  
│   │   └── Auth/           // Authentication Controllers  
│   ├── Middleware/  
│   └── Requests/           // Form Request classes (organized by feature)  
├── Interfaces/             // Interfaces for Repositories and Services  
│   ├── Repositories/  
│   └── Services/  
├── Models/                 // Eloquent Models  
├── Policies/               // Authorization Policies  
├── Providers/              // Service Providers (including AuthServiceProvider, RouteServiceProvider)  
├── Repositories/           // Repository Implementations (organized by technology, e.g., Eloquent)  
│   └── Eloquent/  
└── Services/               // Service Layer classes

## **Cấu trúc thư mục Routes (routes/)**

Các file route được chia nhỏ theo tính năng để dễ quản lý và giảm xung đột khi làm việc nhóm.

routes/  
├── api.php                 // Main API route file (can include feature files)  
├── channels.php  
├── console.php  
├── web.php                 // Main Web route file (can include feature files)  
├── api/                    // API routes organized by feature  
│   ├── auth.php  
│   ├── products.php  
│   ├── categories.php  
│   └── users.php  
└── web/                    // Web routes organized by feature  
    ├── auth.php            // Login, Logout, etc.  
    ├── dashboard.php       // Dashboard page  
    ├── products.php        // Product management routes  
    ├── categories.php      // Category management routes  
    └── users.php           // User management routes

Việc load các file route con này được cấu hình trong app/Providers/RouteServiceProvider.php.

## **Công nghệ sử dụng**

* Laravel 12  
* MySQL  
* Blade Template Engine  
* Blade UI Kit  
* DaisyUI (Plugin cho Tailwind CSS)  
* Spatie Laravel Permission (Quản lý Roles & Permissions)  
* Font Awesome (cho Icons)

## **Cài đặt và Chạy ứng dụng**

1. **Clone repository:**  
   git clone \<địa chỉ repository của bạn\>  
   cd laravel-basic-code-structure

2. **Cài đặt các dependency của PHP:**  
   composer install

3. **Cài đặt các dependency của Node.js (cho Tailwind, DaisyUI, Vite):**  
   npm install

4. **Copy file cấu hình môi trường:**  
   cp .env.example .env

5. **Tạo Application Key:**  
   php artisan key:generate

6. Cấu hình Database:  
   Mở file .env và cấu hình thông tin kết nối database MySQL của bạn (DB\_DATABASE, DB\_USERNAME, DB\_PASSWORD).  
7. Chạy Migrations và Seed dữ liệu:  
   Lệnh này sẽ tạo các bảng cần thiết trong database và điền dữ liệu ban đầu (bao gồm Roles, Permissions, người dùng test, thể loại, sản phẩm).  
   php artisan migrate:fresh \--seed

   * Tài khoản Admin test: admin@example.com / password  
   * Tài khoản Manager test: manager@example.com / password  
   * Tài khoản User test: user@example.com / password  
8. Chạy Storage Link:  
   Để hiển thị ảnh sản phẩm, bạn cần tạo symbolic link từ public/storage đến storage/app/public.  
   php artisan storage:link

9. Chạy Development Server (Vite):  
   Để Tailwind CSS (và DaisyUI) hoạt động, bạn cần chạy Vite.  
   npm run dev

10. Chạy Laravel Development Server:  
    Mở một terminal khác và chạy Laravel server.  
    php artisan serve

11. Truy cập ứng dụng:  
    Mở trình duyệt và truy cập URL hiển thị bởi php artisan serve (thường là http://127.0.0.1:8000).

## **Các khái niệm chính được minh họa**

* **MVC (Model-View-Controller)**  
* **Service Layer**  
* **Repository Pattern** & **Dependency Inversion Principle (DIP)** thông qua Interfaces và Dependency Injection.  
* **Dependency Injection** trong Service Container của Laravel.  
* **Form Requests** cho Validation và Authorization.  
* **Custom Exceptions** cho xử lý lỗi nghiệp vụ.  
* **Authorization** với Spatie Laravel Permission, Gates và Policies.  
* **Route Organization** theo tính năng.  
* Sử dụng **Blade UI Kit** và **DaisyUI** để xây dựng giao diện.

## **Đóng góp**

Các thành viên trong nhóm được khuyến khích đóng góp code, tuân thủ cấu trúc đã định nghĩa. Hãy tạo các nhánh tính năng (feature branches) và gửi Pull Request (PR) để review code trước khi merge.

Chúc nhóm bạn thành công với đồ án và học hỏi được nhiều điều từ cấu trúc code này\!