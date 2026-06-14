<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        if (Categoria::count() > 0) {
            return;
        }

        $categorias = [
            ['nome' => 'Salário',          'cor' => '#00ff88', 'icone' => 'cash',        'tipo' => 'receita'],
            ['nome' => 'Freelance',         'cor' => '#3742fa', 'icone' => 'laptop',      'tipo' => 'receita'],
            ['nome' => 'Investimentos',     'cor' => '#ffd700', 'icone' => 'graph-up',    'tipo' => 'receita'],
            ['nome' => 'Aluguel Recebido',  'cor' => '#a29bfe', 'icone' => 'house',       'tipo' => 'receita'],
            ['nome' => 'Alimentação',       'cor' => '#ff6b35', 'icone' => 'egg-fried',   'tipo' => 'despesa'],
            ['nome' => 'Mercado',           'cor' => '#fd9644', 'icone' => 'cart',        'tipo' => 'despesa'],
            ['nome' => 'Transporte',        'cor' => '#45aaf2', 'icone' => 'car-front',   'tipo' => 'despesa'],
            ['nome' => 'Combustível',       'cor' => '#fc5c65', 'icone' => 'fuel-pump',   'tipo' => 'despesa'],
            ['nome' => 'Moradia',           'cor' => '#8854d0', 'icone' => 'building',    'tipo' => 'despesa'],
            ['nome' => 'Saúde',             'cor' => '#ff4757', 'icone' => 'hospital',    'tipo' => 'despesa'],
            ['nome' => 'Educação',          'cor' => '#2bcbba', 'icone' => 'mortarboard', 'tipo' => 'despesa'],
            ['nome' => 'Lazer',             'cor' => '#fd79a8', 'icone' => 'film',        'tipo' => 'despesa'],
            ['nome' => 'Roupas',            'cor' => '#e17055', 'icone' => 'bag',         'tipo' => 'despesa'],
            ['nome' => 'Contas (Luz/Água)', 'cor' => '#fdcb6e', 'icone' => 'lightning',   'tipo' => 'despesa'],
            ['nome' => 'Internet',          'cor' => '#00cec9', 'icone' => 'wifi',        'tipo' => 'despesa'],
            ['nome' => 'Telefone',          'cor' => '#6c5ce7', 'icone' => 'phone',       'tipo' => 'despesa'],
            ['nome' => 'Assinaturas',       'cor' => '#74b9ff', 'icone' => 'tv',          'tipo' => 'despesa'],
            ['nome' => 'Petshop',           'cor' => '#fd79a8', 'icone' => 'bag-heart',   'tipo' => 'despesa'],
            ['nome' => 'Veterinário',       'cor' => '#55efc4', 'icone' => 'bandaid',     'tipo' => 'despesa'],
            ['nome' => 'Games',             'cor' => '#7c3aed', 'icone' => 'controller',     'tipo' => 'despesa'],
            ['nome' => 'Imposto',           'cor' => '#f43f5e', 'icone' => 'receipt-cutoff', 'tipo' => 'despesa'],
            ['nome' => 'Besteiras',         'cor' => '#c084fc', 'icone' => 'stars',        'tipo' => 'despesa'],
            ['nome' => 'Compras',           'cor' => '#fb923c', 'icone' => 'shop-window',  'tipo' => 'despesa'],
            ['nome' => 'Role',              'cor' => '#f59e0b', 'icone' => 'people',       'tipo' => 'despesa'],
            ['nome' => 'Outros',            'cor' => '#b2bec3', 'icone' => 'three-dots',   'tipo' => 'ambos'],
        ];

        foreach ($categorias as $cat) {
            Categoria::create(array_merge($cat, ['ativo' => true]));
        }
    }
}
