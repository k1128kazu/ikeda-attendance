/** @test */
public function 休憩入できる()
{
$user=User::factory()->create();

$attendance=Attendance::factory()->create([
'user_id'=>$user->id,
'status'=>'working'
]);

$this->actingAs($user);

$this->post('/attendance/break-start');

$this->assertDatabaseHas('attendance_breaks',[
'attendance_id'=>$attendance->id
]);
}