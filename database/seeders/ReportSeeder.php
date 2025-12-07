<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample report 1: Students and Results
        DB::table('reports')->insert([
            'name' => 'Student Results Report',
            'report_details' => json_encode([
                'tables' => [
                    ['students' => ['name', 'age']],
                    ['results' => ['marks_obtained', 'subject_id']],
                ],
                'joins' => [
                    [
                        'join_type' => 'inner',
                        'left_table' => 'students',
                        'left_column' => 'id',
                        'right_table' => 'results',
                        'right_column' => 'student_id',
                    ],
                ],
                'dateTable' => 'students',
            ]),
            'filters' => json_encode([
                [
                    'id' => 'marks_range',
                    'label' => 'Marks Range',
                    'type' => 'number_range',
                    'table' => 'results',
                    'column' => 'marks_obtained',
                    'default' => null,
                    'required' => false,
                    'order' => 1,
                ],
            ]),
            'users' => json_encode(['Admin', 'Teacher']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sample report 2: Subjects and Teachers
        DB::table('reports')->insert([
            'name' => 'Subject Teachers Report',
            'report_details' => json_encode([
                'tables' => [
                    ['subjects' => ['name']],
                    ['teachers' => ['name']],
                ],
                'joins' => [
                    [
                        'join_type' => 'inner',
                        'left_table' => 'subjects',
                        'left_column' => 'id',
                        'right_table' => 'teachers',
                        'right_column' => 'subject_id',
                    ],
                ],
                'dateTable' => 'subjects',
            ]),
            'filters' => json_encode([]),
            'users' => json_encode(['Admin']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sample report 3: Combined Results
        DB::table('reports')->insert([
            'name' => 'Complete Results Analysis',
            'report_details' => json_encode([
                'tables' => [
                    ['students' => ['name']],
                    ['results' => ['marks_obtained']],
                    ['subjects' => ['name']],
                ],
                'joins' => [
                    [
                        'join_type' => 'inner',
                        'left_table' => 'students',
                        'left_column' => 'id',
                        'right_table' => 'results',
                        'right_column' => 'student_id',
                    ],
                    [
                        'join_type' => 'inner',
                        'left_table' => 'results',
                        'left_column' => 'subject_id',
                        'right_table' => 'subjects',
                        'right_column' => 'id',
                    ],
                ],
                'dateTable' => 'students',
            ]),
            'filters' => json_encode([
                [
                    'id' => 'min_marks',
                    'label' => 'Minimum Marks',
                    'type' => 'number_range',
                    'table' => 'results',
                    'column' => 'marks_obtained',
                    'default' => null,
                    'required' => false,
                    'order' => 1,
                ],
            ]),
            'users' => json_encode(['Admin', 'Teacher', 'Staff']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sample report 4: Subjects with Results (the one that was failing)
        DB::table('reports')->insert([
            'name' => 'Subjects Results Report',
            'report_details' => json_encode([
                'tables' => [
                    ['subjects' => ['name']],
                    ['results' => ['student_id', 'subject_id', 'marks_obtained']],
                ],
                'joins' => [
                    [
                        'join_type' => 'inner',
                        'left_table' => 'subjects',
                        'left_column' => 'id',
                        'right_table' => 'results',
                        'right_column' => 'subject_id',
                    ],
                ],
                'dateTable' => 'subjects',
            ]),
            'filters' => json_encode([
                [
                    'id' => 'subject_filter',
                    'label' => 'Subject Name',
                    'type' => 'dropdown',
                    'table' => 'subjects',
                    'column' => 'name',
                    'options_source' => 'query',
                    'options_query' => 'SELECT DISTINCT name FROM subjects',
                    'default' => null,
                    'required' => false,
                    'order' => 1,
                ],
                [
                    'id' => 'passing_marks',
                    'label' => 'Passing Marks (Greater Than)',
                    'type' => 'number_range',
                    'table' => 'results',
                    'column' => 'marks_obtained',
                    'default' => null,
                    'required' => false,
                    'order' => 2,
                ],
            ]),
            'users' => json_encode(['Tania Sporer', 'Werner Johns']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sample report 5: Students with Checkbox Filters
        DB::table('reports')->insert([
            'name' => 'Students Analysis Report',
            'report_details' => json_encode([
                'tables' => [
                    ['students' => ['name', 'age']],
                    ['results' => ['marks_obtained']],
                ],
                'joins' => [
                    [
                        'join_type' => 'inner',
                        'left_table' => 'students',
                        'left_column' => 'id',
                        'right_table' => 'results',
                        'right_column' => 'student_id',
                    ],
                ],
                'dateTable' => 'students',
            ]),
            'filters' => json_encode([
                [
                    'id' => 'age_groups',
                    'label' => 'Age Groups',
                    'type' => 'checkbox',
                    'table' => 'students',
                    'column' => 'age',
                    'options_source' => 'static',
                    'options' => ['15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25'],
                    'default' => null,
                    'required' => false,
                    'order' => 1,
                ],
                [
                    'id' => 'performance_level',
                    'label' => 'Performance Level (Marks >)',
                    'type' => 'number_range',
                    'table' => 'results',
                    'column' => 'marks_obtained',
                    'default' => null,
                    'required' => false,
                    'order' => 2,
                ],
            ]),
            'users' => json_encode(['Admin', 'Teacher', 'Principal']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
