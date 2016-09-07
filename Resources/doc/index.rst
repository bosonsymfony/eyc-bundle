Componente: EyCBundle
=====================


1. Descripción general
----------------------

    Maneja la estructura organizacional y nomencladores  de forma  dinámica,  eficiente y adaptable.
    Esta solución se basa  en el patrón Entidad Atributo Valor (EAV) que le da la facilidad al usuario
    de personalizar sus estructuras en dependencia de sus requerimientos. El componente brinda una serie
    de comandos y  servicios que lo hacen adecuado para la integración con otras soluciones.
    Definir entidades tales como Facultad, la cual tiene atributos como: especialidad, localidad, etc.
    Una vez definida una estructura se pueden obtener instancias de esta estructura tales como, Facultad de medicina
    Mariana Grajales, Especialidad Salud, Ubicada en Ciudad de la Habana.
    Esta estructura dentro cuenta con departamentos docentes que tienen sus atributos y con los cuales
    se puede establecer una relación de precedencia donde podemos especificar subordinaciones, es decir,
    las estructuras que pueden contener a otras, ejemplo:

         Facultad
          |__Departamento1
          |__Departamento2
              |__Disciplina


2. Instalación
--------------

    1. Copiar el componente dentro de la carpeta `vendor/boson/eyc-bundle/UCI/Boson`.
    2. Registrarlo en el archivo `app/autoload.php` de la siguiente forma:

       .. code-block:: php

           // ...
           $loader = require __DIR__ . '/../vendor/autoload.php';
           $loader->add("UCI\\Boson\\EyCBundle", __DIR__ . '/../vendor/boson/eyc-bundle');
           // ...

    3. Activarlo en el kernel de la siguiente manera:

       .. code-block:: php

           // app/AppKernel.php
           public function registerBundles()
           {
               return array(
                   // ...
                   new UCI\Boson\EyCBundle\EyCBundle(),
                   // ...
               );
           }

3. Especificación funcional
---------------------------
    Para utilizar un nomenclador es necesario haberlo definido previamente, tanto estructuralmente como sus
    instancias, al igual que para las estructuras.
    Un campo de un nomenclador puede estar vinculado a otro nomenclador, el cual debe estar definido con
    anterioridad.
    Entre las estructuras se pueden definir las subordinaciones, que estructura puede contener a otras, por
    ejemplo, una facultad puede contener departamentos en su nivel de jerarquía.
    El componente provee una serie de comandos que permiten ponerlo a punto:


Para gestionar los nomencladores:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    -boson:eyc:insertarNom   Inserta un nomenclador, se debe definir el nombre

    -boson:eyc:mostrarNom    Mostrar un nomenclador,  requiere el id del nomenclador a mostrar

    -boson:eyc:eliminarNom    Eliminar un nomenclador,  requiere el id del nomenclador a eliminar

    -boson:eyc:insertarCampoNom   Inserta un campo de un nomenclador, se deben definir los atributos:

               - *campo* : {*nombre, tipo, descripción, vinculado, nomencladorVin*}
               - *nombre*: es el nombre del campo
               - *tipo*: tipo de dato {"bool", "integer", "double", "string", "date"}
               - *descripción*: para qué se usa el campo
               - *vinculado*: true o false si está vinculado con un nomenclador
               - *nomencladorVin*: identificador del nomenclador vinculado

    -boson:eyc:mostrarCamposNom   Muestra los campos de un nomenclador, requiere el id de un nomenclador y muestra sus campos

    -boson:eyc:eliminarCampoNom    Elimina un campo de un nomenclador, requiere el id de un campo de  nomenclador y lo elimina

    -boson:eyc:insertarNomOp   Inserta una instancia de un nomenclador, requiere el id del nomenclador y los valores de los atributos correspondientes

    -boson:eyc:eliminarNomOp   Requiere el id de la instancia de nomenclador a eliminar

    -boson:eyc:mostrarValoresCamposNomOp   Requiere el id de la instancia del nomenclador para mostrar sus campos

Para gestionar las estructuras:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    -boson:eyc:insertarEstruc   Inserta una estructura, para esto se tienen que definir sus atributos, nombre, si es raíz y los campos:

              - *campo*: {nombre, tipo, descripción, vinculado, nomencladorVin}
              - *nombre*: Es el nombre del campo
              - *tipo*: Tipo de dato {"bool", "integer", "double", "string", "date"}
              - *descripción*: para que se usa el campo
              - *vinculado*: true o false si está vinculado con un nomenclador
              - *nomencladorVin*: id del nomenclador vinculado


    -boson:eyc:insertarEstrucSub    Inserta dado un id de una  estructura, las estructuras subordinadas: ids = [id1, id2,...,idn]

    -boson:eyc:mostrarEstrucSub   Muestra las estructuras subordinadas

    -boson:eyc:mostrarJerarquiaEstrucOp   Muestra la jerarquía de estructuras

    -boson:eyc:insertarEstrucOp    Inserta una instancia de una estructura, los atributos están en correspondencia            con los campos definido para la estructura especificada en id  que recibe como parámetro

    -boson:eyc:eliminarCampoNom    Elimina un campo de un nomenclador, recibe un id de un campo de  nomenclador y lo elimina

    -boson:eyc:mostrarValoresCamposEstrucOp   Requiere el id de la instancia de la estructura para mostrar sus campos

    Los comandos están programados de forma interactiva para una mejor comprensión de los mismos. Ejecútelos en la consola

3.1. Requisitos funcionales
~~~~~~~~~~~~~~~~~~~~~~~~~~~

    R1 Gestionar conceptos
    R1.1 Adicionar conceptos
    R1.2 Modificar conceptos
    R1.3 Eliminar conceptos
    R1.4 Crear relaciones entre conceptos

    R2 Gestionar campos
    R2.1 Adicionar campos a conceptos
    R2.2 Modificar campos a conceptos
    R2.3 Eliminar campos a conceptos

    R3 Gestionar elementos
    R3.1 Adicionar datos a conceptos
    R3.2 Modificar datos a conceptos
    R3.3 Eliminar datos a conceptos

    R4 Gestionar nomencladores
    R4.1 Adicionar nomenclador
    R4.2 Adicionar campos o atributos a nomenclador
    R4.3 Adicionar instancia a nomenclador
    R4.4 Modificar instancia a nomenclador
    R4.5 Eliminar instancia a nomenclador


    Estos requisitos  se aplican para la gestión de las entidades y los nomencladores, se implementan
    en el Directorio Service. Consultar su implementación en dicho directorio.

4. Servicios que brinda
-----------------------

   - servicio nomenclador
   - servicio estructura

5. Servicios de los que depende
-------------------------------

 -doctrine
 -validator

---------------------------------------------

:Versión: 1.0 17/7/2015
:Autores:

Contribuidores
--------------

:Entidad: Universidad de las Ciencias Informáticas. Centro de Informatización de Entidades.


Licencia
--------

