<?php
namespace Database\Seeders;
use App\Models\{Booking,Category,Movie,Product,Room,Seat,Showtime,User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(['email'=>'admin@cinemax.test'], ['name'=>'Quản trị CINEMAX','phone'=>'0900000001','password'=>'Demo@12345','role'=>'admin','status'=>'hoat_dong']);
        User::firstOrCreate(['email'=>'manager@cinemax.test'], ['name'=>'Quản lý CINEMAX','phone'=>'0900000002','password'=>'Demo@12345','role'=>'quan_ly','status'=>'hoat_dong']);
        User::firstOrCreate(['email'=>'customer@cinemax.test'], ['name'=>'Khách hàng demo','phone'=>'0900000003','password'=>'Demo@12345','role'=>'khach_hang','status'=>'hoat_dong']);
        $cats=[];
        foreach (['Hành động','Hoạt hình','Phiêu lưu','Tình cảm','Gia đình','Tâm lý','Kinh dị'] as $n) $cats[$n]=Category::firstOrCreate(['name'=>$n])->id;
        $entries=[
            ['Thám tử lừng danh Conan: Dư ảnh của độc nhãn','Một bí ẩn tại vùng núi tuyết kéo Conan vào hành trình truy tìm sự thật.',110,'assets/posters/conan.jpg',['Hoạt hình','Hành động','Phiêu lưu']],
            ['Nhà Bà Nữ','Những va chạm thế hệ và bí mật trong một gia đình Việt.',102,'assets/posters/nha-ba-nu.jpg',['Tình cảm','Gia đình','Tâm lý']],
            ['Elemental: Xứ sở các nguyên tố','Lửa và nước gặp nhau trong câu chuyện hoạt hình đầy màu sắc.',101,'assets/posters/elemental.jpg',['Hoạt hình','Gia đình']],
            ['Thiên Đường Máu','Hành trình sinh tồn và đấu tranh của những con người mắc kẹt trong cạm bẫy.',112,'assets/posters/thien-duong-mau.jpg',['Tâm lý','Hành động']],
        ];
        $movies=[];
        foreach($entries as $i=>$a){
            $movie=Movie::firstOrCreate(['title'=>$a[0]],['description'=>$a[1],'duration'=>$a[2],'release_date'=>now()->subDays(30+$i*12)->toDateString(),'poster'=>$a[3], 'status'=>'dang_chieu']);
            $movie->categories()->syncWithoutDetaching(array_map(fn($k)=>$cats[$k], $a[4])); $movies[]=$movie;
        }
        foreach(['Phòng 01','Phòng 02','Phòng VIP'] as $i=>$name){
            $room=Room::firstOrCreate(['name'=>$name],['total_seats'=>0]);
            if($room->seats()->count()===0){
                foreach(range('A','E') as $r) foreach(range(1,8) as $c) {
                    $vip=$r==='D'||$r==='E'||$i===2;
                    Seat::create(['room_id'=>$room->id,'seat_code'=>sprintf('%s%02d',$r,$c),'type'=>$vip?'vip':'thuong','price'=>$vip?110000:85000]);
                }
                $room->update(['total_seats'=>$room->seats()->count()]);
            }
            foreach(range(0,6) as $day){
                $date=now()->addDays($day)->toDateString();
                foreach([['09:00:00',0],['13:00:00',1],['17:00:00',2],['20:30:00',3]] as $j=>$slot){
                    $movie=$movies[($slot[1]+$i+$day)%count($movies)];
                    Showtime::firstOrCreate(['room_id'=>$room->id,'show_date'=>$date,'start_time'=>$slot[0]], ['movie_id'=>$movie->id,'status'=>'dang_hoat_dong']);
                }
            }
        }
        foreach([
            ['Bắp rang bơ','Bắp giòn thơm vị bơ',45000],
            ['Bắp caramel','Bắp phủ caramel ngọt dịu',55000],
            ['Coca-Cola','Nước ngọt có gas',30000],
            ['Combo đôi','2 nước ngọt và 1 bắp lớn',105000],
            ['Combo gia đình','2 bắp và 3 nước',185000],
        ] as $p) Product::firstOrCreate(['name'=>$p[0]],['description'=>$p[1],'price'=>$p[2],'status'=>'dang_ban']);
    }
}
