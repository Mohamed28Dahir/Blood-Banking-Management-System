<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema;\n
class CreateDonorEligibilityTable extends Migration
{
    public function up()
    {
        Schema::create('donor_eligibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('age');
            $table->decimal('weight', 5, 2);
            $table->enum('eligibility_status', ['eligible', 'ineligible']);
            $table->string('ineligibility_reason')->nullable();
            $table->date('last_donation_date')->nullable();
            $table->date('next_eligible_date')->nullable();
            $table->boolean('is_permanent_ineligible')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('donor_eligibility');
    }
}