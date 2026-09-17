















BEGIN;


INSERT INTO actividad_terreno (id_actividad_terreno, nombre) VALUES
    (1, 'Inspección'),
    (2, 'Siembra'),
    (3, 'Seguimiento'),
    (4, 'Resiembra')
ON CONFLICT (id_actividad_terreno) DO NOTHING;

INSERT INTO tipo_deposito (id_tipo_deposito, descripcion) VALUES
    (1, 'Piscina abandonada'),
    (2, 'Aguas estancadas'),
    (3, 'Fuente o pila de agua'),
    (4, 'Construcción abandonada')
ON CONFLICT (id_tipo_deposito) DO NOTHING;



INSERT INTO comuna (id_comuna, nombre) VALUES (1, 'Comuna 1'), (11, 'Comuna 11')
    ON CONFLICT (id_comuna) DO NOTHING;

INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES
    (1, 'Barrio Centro', 1),
    (2, 'San Carlos', 11)
    ON CONFLICT (id_barrio) DO NOTHING;

INSERT INTO sitio_terreno (id_sitio, direccion, latitud, longitud) VALUES
    (1, 'Calle 30 #36-5, San Carlos', 3.420000, -76.520000),
    (2, 'Carrera 15 #8-20', 3.451000, -76.531000)
ON CONFLICT (id_sitio) DO NOTHING;

INSERT INTO sitio_barrio (id_sitio_barrio, id_sitio, id_barrio) VALUES
    (1, 1, 2),
    (2, 2, 1)
ON CONFLICT (id_sitio_barrio) DO NOTHING;

INSERT INTO deposito (id_deposito, id_sitio, id_tipo_deposito) VALUES
    (1, 1, 2),
    (2, 1, 3),
    (3, 2, 1),
    (4, 2, 4)
ON CONFLICT (id_deposito) DO NOTHING;

COMMIT;
