/** @test */
public function 修正申請作成()
{
$user=User::factory()->create();

$attendance=Attendance::factory()->create([
'user_id'=>$user->id
]);

$this->actingAs($user);

$this->post('/attendance/correction',[
'attendance_id'=>$attendance->id,
'request_clock_in'=>'09:00',
'request_clock_out'=>'18:00',
'request_note'=>'修正'
]);

$this->assertDatabaseHas('attendance_correction_requests',[
'attendance_id'=>$attendance->id
]);
}