<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateInventarioStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventario:update-status';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Actualiza automáticamente el estado de los medicamentos basado en la fecha de expiración';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando actualización de estados de medicamentos...');

        try {
            $hoy = Carbon::now()->format('Y-m-d');

            // Actualizar medicamentos vencidos
            $vencidosCount = DB::table('tbl_far_lote')
                ->where('fecha_expiracion', '<', $hoy)
                ->where('estado', '!=', 'VENCIDO')
                ->update(['estado' => 'VENCIDO']);

            if ($vencidosCount > 0) {
                $this->info("✓ {$vencidosCount} medicamento(s) marcado(s) como VENCIDO");
            }

            // Actualizar medicamentos sin stock
            $agotadosCount = DB::table('tbl_far_lote')
                ->where('cantidad_actual', 0)
                ->where('estado', '!=', 'AGOTADO')
                ->update(['estado' => 'AGOTADO']);

            if ($agotadosCount > 0) {
                $this->info("✓ {$agotadosCount} medicamento(s) marcado(s) como AGOTADO");
            }

            // Actualizar medicamentos que ya no están vencidos (si la fecha cambió)
            $activadosCount = DB::table('tbl_far_lote')
                ->where('fecha_expiracion', '>=', $hoy)
                ->where('cantidad_actual', '>', 0)
                ->where('estado', 'VENCIDO')
                ->update(['estado' => 'ACTIVO']);

            if ($activadosCount > 0) {
                $this->info("✓ {$activadosCount} medicamento(s) reactivado(s) a ACTIVO");
            }

            $this->info('Actualización completada exitosamente.');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error al actualizar estados: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
