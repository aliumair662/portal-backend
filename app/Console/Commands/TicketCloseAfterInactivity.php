<?php

namespace App\Console\Commands;

use App\Enums\TicketType;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TicketCloseAfterInactivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticket:inactivity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marks tickets as closed after a certain period of activity';

    /**
     * How much days a ticket should be inactive for
     *
     * @var int
     */
    protected $days_of_inactivity = 14;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $inactive_tickets = Ticket::where('updated_at', '<', Carbon::now()->subDays( $this->days_of_inactivity )->toDateTimeString() )->where('status', '!=', TicketType::CLOSED)->get();

        if( $inactive_tickets->count() ){

            foreach( $inactive_tickets as $inactive_ticket ){

                $this->info($inactive_ticket->id . ' was marked as closed.');
                $inactive_ticket->status = TicketType::CLOSED;
                $inactive_ticket->save();

            }
        }
    }
}
