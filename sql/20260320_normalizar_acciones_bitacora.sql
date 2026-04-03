-- Normaliza acciones genericas historicas en tbl_seg_tipo_accion
-- usando el modulo (tbl_seg_formulario.descripcion).

UPDATE tbl_seg_tipo_accion ta
INNER JOIN tbl_seg_formulario f ON f.id_formulario = ta.id_formulario
SET
    ta.accion = CASE
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'CREAR' THEN 'REGISTRAR PACIENTE'
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTAR PACIENTES'
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZAR PACIENTE'
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINAR PACIENTE'

        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'CREAR' THEN 'REGISTRAR REACCION'
        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTAR REACCIONES'
        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZAR REACCION'
        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINAR REACCION'

        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'CREAR' THEN 'REGISTRAR USUARIO'
        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTAR USUARIOS'
        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZAR USUARIO'
        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINAR USUARIO'

        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'CREAR' THEN 'CREAR PARAMETRO'
        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTAR PARAMETROS'
        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZAR PARAMETRO'
        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINAR PARAMETRO'

        WHEN UPPER(f.descripcion) = 'PREGUNTAS' AND UPPER(ta.accion) = 'CREAR' THEN 'CREAR PREGUNTA'
        WHEN UPPER(f.descripcion) = 'PREGUNTAS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTAR PREGUNTAS'
        WHEN UPPER(f.descripcion) = 'PREGUNTAS' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZAR PREGUNTA'
        WHEN UPPER(f.descripcion) = 'PREGUNTAS' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINAR PREGUNTA'

        WHEN UPPER(f.descripcion) = 'RESPALDOS' AND UPPER(ta.accion) = 'CREAR' THEN 'CREAR RESPALDO'
        WHEN UPPER(f.descripcion) = 'RESPALDOS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTAR RESPALDOS'

        ELSE ta.accion
    END,
    ta.descripcion = CASE
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'CREAR' THEN 'REGISTRO DE PACIENTE'
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTA DE PACIENTES'
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZACION DE PACIENTE'
        WHEN UPPER(f.descripcion) = 'PACIENTES' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINACION DE PACIENTE'

        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'CREAR' THEN 'REGISTRO REACCION ADVERSA'
        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTA REACCIONES ADVERSAS'
        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZACION DE REACCION'
        WHEN UPPER(f.descripcion) = 'REACCIONES ADVERSAS' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINACION DE REACCION'

        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'CREAR' THEN 'REGISTRO DE USUARIO'
        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTA DE USUARIOS'
        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZACION DE USUARIO'
        WHEN UPPER(f.descripcion) = 'USUARIOS' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINACION DE USUARIO'

        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'CREAR' THEN 'CREACION DE PARAMETRO'
        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'CONSULTAR' THEN 'CONSULTA DE PARAMETROS'
        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'ACTUALIZAR' THEN 'ACTUALIZACION PARAMETRO'
        WHEN UPPER(f.descripcion) = 'PARAMETROS' AND UPPER(ta.accion) = 'ELIMINAR' THEN 'ELIMINACION PARAMETRO'

        ELSE ta.descripcion
    END,
    ta.fecha_modificacion = NOW(),
    ta.usuario_modificacion = COALESCE(ta.usuario_modificacion, ta.usuario_creacion)
WHERE UPPER(ta.estado) = 'ACTIVO';
