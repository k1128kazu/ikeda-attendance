/** @test */
public function 退勤できる()
{
$user=User::factory()->create();

$attendance=Attendance::factory()->create([
'user_id'=>$user->id,
'status'=>'working'
]);

$this->actingAs($user);

$this->post('/attendance/clockout');

$this->assertDatabaseHas('attendances',[
'id'=>$attendance->id,
'status'=>'finished'
]);
}