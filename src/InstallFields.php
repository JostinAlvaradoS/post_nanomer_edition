<?php

/**
 * @file
 * Create field storage and field instances for Post Nanomer Edition module.
 */

namespace Drupal\post_nanomer_edition;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\pathauto\Entity\PathautoPattern;

class InstallFields {

  /**
   * Create all fields for the Post Nanomer Edition content type.
   */
  public static function createFields() {
    // Definición de campos para el Post Nanomer Edition
    $secciones = [
      'hero_titulo' => ['label' => 'Hero - Título Principal', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      'hero_descripcion' => ['label' => 'Hero - Subtítulo', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      
      'descripcion_contenido' => ['label' => 'Description - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      'fecha_lugar' => ['label' => 'Fecha y Lugar', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      
      'flickr_iframes' => ['label' => 'Flickr - Iframes (uno por línea)', 'type' => 'text_long', 'cardinality' => -1, 'required' => FALSE],
      
      'objetivos_edicion' => ['label' => 'Objectives of the edition', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      'academic_titulo' => ['label' => 'Academic - Título del Item', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'academic_descripcion' => ['label' => 'Academic - Descripción del Item', 'type' => 'text_long', 'cardinality' => -1, 'required' => FALSE],
      
      'edicion_imagen' => ['label' => 'Imagen de la Edición (para carrusel)', 'type' => 'image', 'cardinality' => 1, 'required' => FALSE],
    ];

    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        \Drupal::logger('post_nanomer_edition')->info('Creating field @name of type @type', [
          '@name' => $field_name_full,
          '@type' => $field_config['type'],
        ]);

        // Crear field storage
        $field_storage = FieldStorageConfig::loadByName('node', $field_name_full);
        if (!$field_storage) {
          $field_storage = FieldStorageConfig::create([
            'field_name' => $field_name_full,
            'entity_type' => 'node',
            'type' => $field_config['type'],
            'cardinality' => $field_config['cardinality'],
          ]);
          $field_storage->save();
          \Drupal::logger('post_nanomer_edition')->info('Field storage created: @name', ['@name' => $field_name_full]);
        } else {
          \Drupal::logger('post_nanomer_edition')->info('Field storage already exists: @name', ['@name' => $field_name_full]);
        }

        // Crear field instance
        $field = FieldConfig::loadByName('node', 'post_nanomer_edition', $field_name_full);
        if (!$field) {
          $field = FieldConfig::create([
            'field_storage' => $field_storage,
            'bundle' => 'post_nanomer_edition',
            'label' => $field_config['label'],
            'required' => $field_config['required'],
            'translatable' => TRUE,
          ]);
          
          // Deshabilitar text processing para campos text_long
          if ($field_config['type'] === 'text_long') {
            $field->setThirdPartySetting('field_ui', 'default_widget', 'text_textarea');
          }
          
          $field->save();
          \Drupal::logger('post_nanomer_edition')->info('Field config created: @name', ['@name' => $field_name_full]);
        } else {
          \Drupal::logger('post_nanomer_edition')->info('Field config already exists: @name', ['@name' => $field_name_full]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('post_nanomer_edition')->error('Error creating field @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    // Configurar display (form y view)
    $entity_form_display = EntityFormDisplay::load('node.post_nanomer_edition.default');
    if (!$entity_form_display) {
      $entity_form_display = EntityFormDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'post_nanomer_edition',
        'mode' => 'default',
        'status' => TRUE,
      ]);
      \Drupal::logger('post_nanomer_edition')->info('Created new EntityFormDisplay for post_nanomer_edition');
    } else {
      \Drupal::logger('post_nanomer_edition')->info('Loaded existing EntityFormDisplay for post_nanomer_edition');
    }

    $entity_view_display = EntityViewDisplay::load('node.post_nanomer_edition.default');
    if (!$entity_view_display) {
      $entity_view_display = EntityViewDisplay::create([
        'targetEntityType' => 'node',
        'bundle' => 'post_nanomer_edition',
        'mode' => 'default',
        'status' => TRUE,
      ]);
      \Drupal::logger('post_nanomer_edition')->info('Created new EntityViewDisplay for post_nanomer_edition');
    } else {
      \Drupal::logger('post_nanomer_edition')->info('Loaded existing EntityViewDisplay for post_nanomer_edition');
    }

    // Configurar los widgets de formulario y vista
    $weight = 0;
    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        \Drupal::logger('post_nanomer_edition')->info('Configuring widget for @name (type: @type)', [
          '@name' => $field_name_full,
          '@type' => $field_config['type'],
        ]);

        // Form display
        if ($field_config['type'] === 'image') {
          $entity_form_display->setComponent($field_name_full, [
            'type' => 'image_image',
            'weight' => $weight++,
            'settings' => [
              'progress_indicator' => 'throbber',
              'preview_image_style' => 'thumbnail',
            ],
            'region' => 'content',
          ]);
          \Drupal::logger('post_nanomer_edition')->info('Image widget configured: @name', ['@name' => $field_name_full]);
        } elseif ($field_config['type'] === 'email') {
          $entity_form_display->setComponent($field_name_full, [
            'type' => 'email_default',
            'weight' => $weight++,
            'settings' => [
              'placeholder' => '',
            ],
          ]);
          \Drupal::logger('post_nanomer_edition')->info('Email widget configured: @name', ['@name' => $field_name_full]);
        } else {
          $entity_form_display->setComponent($field_name_full, [
            'type' => $field_config['type'] === 'text_long' ? 'text_textarea' : 'text_textfield',
            'weight' => $weight++,
            'settings' => [
              'rows' => 4,
            ],
          ]);
          \Drupal::logger('post_nanomer_edition')->info('Text widget configured: @name', ['@name' => $field_name_full]);
        }

        // View display - hide all fields (usaremos plantilla personalizada)
        $entity_view_display->removeComponent($field_name_full);
      } catch (\Exception $e) {
        \Drupal::logger('post_nanomer_edition')->error('Error configuring widget for @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    $entity_form_display->save();
    $entity_view_display->save();

    // Crear el patrón de Pathauto de forma programática.
    try {
      if (!PathautoPattern::load('post_nanomer_edition_pattern')) {
        $pattern = PathautoPattern::create([
          'id' => 'post_nanomer_edition_pattern',
          'label' => 'Pattern for Post Nanomer Edition nodes',
          'type' => 'canonical_entities:node',
          'pattern' => 'post-edicion/[node:title]',
          'selection_criteria' => [
            'node_bundle' => [
              'id' => 'entity_bundle:node',
              'bundles' => [
                'post_nanomer_edition' => 'post_nanomer_edition',
              ],
              'negate' => false,
              'context_mapping' => [
                'node' => 'node',
              ],
            ],
          ],
          'selection_logic' => 'and',
          'weight' => 0,
        ]);
        $pattern->save();
        \Drupal::logger('post_nanomer_edition')->info('Pathauto pattern created: post_nanomer_edition_pattern');
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('post_nanomer_edition')->error('Error creating Pathauto pattern: @error', ['@error' => $e->getMessage()]);
    }
    
    \Drupal::logger('post_nanomer_edition')->info('Fields installation completed successfully');
  }

  /**
   * Delete all fields created for the Post Nanomer Edition content type.
   */
  public static function deleteFields() {
    \Drupal::logger('post_nanomer_edition')->info('=== STARTING FIELD DELETION ===');

    // Eliminar el patrón de Pathauto.
    try {
      $pattern = PathautoPattern::load('post_nanomer_edition_pattern');
      if ($pattern) {
        $pattern->delete();
        \Drupal::logger('post_nanomer_edition')->info('Pathauto pattern deleted: post_nanomer_edition_pattern');
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('post_nanomer_edition')->error('Error deleting Pathauto pattern: @error', ['@error' => $e->getMessage()]);
    }

    // Definición de campos para el Post Nanomer Edition
    $secciones = [
      'hero_titulo' => ['label' => 'Hero - Título Principal', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      'hero_descripcion' => ['label' => 'Hero - Subtítulo', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      
      'descripcion_contenido' => ['label' => 'Description - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      'fecha_lugar' => ['label' => 'Fecha y Lugar', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      
      'flickr_iframes' => ['label' => 'Flickr - Iframes (uno por línea)', 'type' => 'text_long', 'cardinality' => -1, 'required' => FALSE],
      
      'objetivos_edicion' => ['label' => 'Objectives of the edition', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      'academic_titulo' => ['label' => 'Academic - Título del Item', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      'academic_descripcion' => ['label' => 'Academic - Descripción del Item', 'type' => 'text_long', 'cardinality' => -1, 'required' => FALSE],
      
      'edicion_imagen' => ['label' => 'Imagen de la Edición (para carrusel)', 'type' => 'image', 'cardinality' => 1, 'required' => FALSE],
    ];

    foreach ($secciones as $field_name => $field_config) {
      
      // Criterios (Repetible simple)
      'criterios_items' => ['label' => 'Criterios - Item individual', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      
      'becas_contenido' => ['label' => 'Becas - Contenido', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
      
      // Compromisos
      'compromisos_descripcion' => ['label' => 'Compromisos - Descripción', 'type' => 'string', 'cardinality' => -1, 'required' => FALSE],
      
      'edicion_imagen' => ['label' => 'Imagen de la Edición', 'type' => 'image', 'cardinality' => 1, 'required' => FALSE],

      'contacto_email' => ['label' => 'Contacto - Email', 'type' => 'string', 'cardinality' => 1, 'required' => FALSE],
      'contacto_contenido' => ['label' => 'Contacto - Información Adicional', 'type' => 'text_long', 'cardinality' => 1, 'required' => FALSE],
    ];

    // PASO 1: Eliminar EntityFormDisplay y EntityViewDisplay (PRIMERO)
    \Drupal::logger('post_nanomer_edition')->info('STEP 1: Deleting EntityFormDisplay and EntityViewDisplay');
    try {
      $entity_form_display = EntityFormDisplay::load('node.post_nanomer_edition.default');
      if ($entity_form_display) {
        $entity_form_display->delete();
        \Drupal::logger('post_nanomer_edition')->info('✓ EntityFormDisplay deleted successfully');
      } else {
        \Drupal::logger('post_nanomer_edition')->info('EntityFormDisplay not found');
      }

      $entity_view_display = EntityViewDisplay::load('node.post_nanomer_edition.default');
      if ($entity_view_display) {
        $entity_view_display->delete();
        \Drupal::logger('post_nanomer_edition')->info('✓ EntityViewDisplay deleted successfully');
      } else {
        \Drupal::logger('post_nanomer_edition')->info('EntityViewDisplay not found');
      }
    } catch (\Exception $e) {
      \Drupal::logger('post_nanomer_edition')->error('Error deleting displays: @error', [
        '@error' => $e->getMessage(),
      ]);
    }

    // PASO 2: Eliminar FieldConfig (SEGUNDO)
    \Drupal::logger('post_nanomer_edition')->info('STEP 2: Deleting FieldConfig instances');
    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        $field = FieldConfig::loadByName('node', 'post_nanomer_edition', $field_name_full);
        if ($field) {
          $field->delete();
          \Drupal::logger('post_nanomer_edition')->info('✓ FieldConfig deleted: @name', ['@name' => $field_name_full]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('post_nanomer_edition')->error('Error deleting FieldConfig @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    // PASO 3: Eliminar FieldStorageConfig (TERCERO)
    \Drupal::logger('post_nanomer_edition')->info('STEP 3: Deleting FieldStorageConfig definitions');
    foreach ($secciones as $field_name => $field_config) {
      $field_name_full = 'field_' . $field_name;

      try {
        $field_storage = FieldStorageConfig::loadByName('node', $field_name_full);
        if ($field_storage) {
          $field_storage->delete();
          \Drupal::logger('post_nanomer_edition')->info('✓ FieldStorage deleted: @name', ['@name' => $field_name_full]);
        }
      } catch (\Exception $e) {
        \Drupal::logger('post_nanomer_edition')->error('Error deleting FieldStorage @name: @error', [
          '@name' => $field_name_full,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    \Drupal::logger('post_nanomer_edition')->info('=== FIELD DELETION COMPLETED ===');
  }

}
