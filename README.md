# 📚 **Database Library** | **Librería de Base de Datos**

A lightweight and flexible PHP library for managing databases in **YAML**, **JSON**, and **SQLite** formats. Ideal for developers who need a simple solution to manage structured data in various formats, whether in small projects, applications, or even plugins for platforms like **PocketMine**.

---

Una librería de PHP flexible y ligera para manejar bases de datos en formato **YAML**, **JSON**, y **SQLite**. Ideal para desarrolladores que necesiten una solución simple para gestionar datos estructurados en distintos formatos, ya sea en pequeños proyectos, aplicaciones, o incluso en plugins para plataformas como **PocketMine**.

---

## 🎯 **Features** | **Características**

- 🚀 **Multi-format compatibility**: Supports **YAML**, **JSON**, and **SQLite**.
- ⚡ **Cache usage**: Improves performance using in-memory cache (optional).
- 🔐 **Transactions**: Supports transactions when using cache.
- 🛠️ **Easy integration**: Uses the **Factory** pattern to select the database type without modifying core logic.

---

- 🚀 **Compatibilidad con múltiples formatos**: Soporte para **YAML**, **JSON**, y **SQLite**.
- ⚡ **Uso de caché**: Mejora el rendimiento utilizando caché en memoria (opcional).
- 🔐 **Transacciones**: Soporte para transacciones cuando se usa la caché.
- 🛠️ **Fácil integración**: Utiliza el patrón **Factory** para elegir el tipo de base de datos sin modificar la lógica principal.

---

## ⚙️ **Requirements** | **Requisitos**

- **PHP 7.4** or higher.
- Required PHP extensions:
  - `ext-json`
  - `ext-yaml`
  - `ext-sqlite3`

---

- **PHP 7.4** o superior.
- Extensiones de PHP requeridas:
  - `ext-json`
  - `ext-yaml`
  - `ext-sqlite3`

---

## 🚀 **Installation** | **Instalación**

### **Using Composer** | **Usando Composer**

1. Make sure you have **Composer** installed. If not, install it from [here](https://getcomposer.org/).
2. Run the following command in your project root:

   ```bash
   composer require nozell/database-library
   ```

3. Composer will download the library and autoload the classes. You're ready to go!

---

1. Asegúrate de tener **Composer** instalado. Si no lo tienes, puedes instalarlo desde [aquí](https://getcomposer.org/).
2. Ejecuta el siguiente comando en la raíz de tu proyecto:

   ```bash
   composer require nozell/database
   ```

3. ¡Composer descargará la librería y autogenerará el autoload de clases! Ya estás listo para comenzar.

---

### 🛠️ **Manual Installation** | **Instalación Manual**

1. Download or clone the library repository.
2. Include Composer's autoload file in your project:

   ```php
   require 'path/to/database-library/vendor/autoload.php';
   ```

3. The library is now ready to use in your project.

---

1. Descarga o clona el repositorio de la librería.
2. Incluye el archivo de autoload de **Composer** en tu proyecto:

   ```php
   require 'path/to/database-library/vendor/autoload.php';
   ```

3. La librería estará lista para usar en tu proyecto.

---

## 🧑‍💻 **Basic Usage** | **Uso Básico**

### **Database Initialization** | **Inicialización de la Base de Datos**

To create a database instance, use the `DatabaseFactory::create()` method, which allows you to choose the storage type:

---

Para crear una instancia de la base de datos, utiliza el método `DatabaseFactory::create()`, que permite elegir el tipo de almacenamiento:

---

```php
use Nozell\Database\DatabaseFactory;

// Create a YAML database | Crear una base de datos en YAML
$db = DatabaseFactory::create('path/to/data.yml', 'yaml');

// Create a JSON database | Crear una base de datos en JSON
$db = DatabaseFactory::create('path/to/data.json', 'json');

// Create an SQLite database | Crear una base de datos en SQLite
$db = DatabaseFactory::create('path/to/data.db', 'sqlite');
```

---

### 💾 **Saving Data** | **Guardar Datos**

To save data, use the `set()` method. You can define sections and keys to structure the information:

---

Para guardar datos en la base de datos, utiliza el método `set()`. Puedes definir secciones y claves para estructurar la información:

---

```php
$db->set("players", "Steve", ["kills" => 10, "deaths" => 2]);
$db->set("players", "Alex", ["kills" => 15, "deaths" => 3]);
```

---

### 🔍 **Retrieving Data** | **Obtener Datos**

To retrieve saved data, use the `get()` method:

---

Para obtener un valor guardado, utiliza el método `get()`:

---

```php
$data = $db->get("players", "Steve");
if ($data !== null) {
    echo "Steve has " . $data["kills"] . " kills and " . $data["deaths"] . " deaths.";
}
```

---

```php
$data = $db->get("jugadores", "Steve");
if ($data !== null) {
    echo "Steve tiene " . $data["kills"] . " kills y " . $data["deaths"] . " muertes.";
}
```

---

### 🗑️ **Deleting Data** | **Eliminar Datos**

To delete a specific entry from the database, use the `delete()` method:

---

Si necesitas eliminar una entrada específica de la base de datos, usa el método `delete()`:

---

```php
$db->delete("players", "Alex");
```

---

```php
$db->delete("jugadores", "Alex");
```

---

### 🔐 **Transactions (optional)** | **Transacciones (opcional)**

If you enable cache when creating the database, you can use transactions to group several operations:

---

Si habilitas la caché al crear la base de datos, puedes usar transacciones para agrupar varias operaciones:

---

```php
$db->startTransaction();
$db->set("players", "Steve", ["kills" => 11]);
$db->set("players", "Alex", ["kills" => 17]);
$db->commitTransaction();  // Saves changes | Guarda los cambios
// $db->rollbackTransaction(); // Rollback changes | Deshacer los cambios
```

---

```php
$db->startTransaction();
$db->set("jugadores", "Steve", ["kills" => 11]);
$db->set("jugadores", "Alex", ["kills" => 17]);
$db->commitTransaction();  // Guarda los cambios
// $db->rollbackTransaction(); // Para deshacer los cambios
```

---

## ⚙️ **Advanced Options** | **Opciones Avanzadas**

### 💡 **Using Cache** | **Uso de Caché**

By default, the library uses cache to improve performance. This means data is loaded into memory and written to disk only when necessary. You can disable cache if you prefer to write directly to disk:

---

Por defecto, la librería utiliza caché para mejorar el rendimiento. Los datos se cargan en memoria y se escriben en disco solo cuando es necesario. Puedes desactivar la caché si prefieres escribir directamente en disco:

---

```php
$db = DatabaseFactory::create('path/to/data.yml', 'yaml', false);
```

---

### ⚡ **SQLite Support** | **Soporte para SQLite**

In addition to YAML and JSON, you can use SQLite as a lightweight database for larger projects requiring SQL queries:

---

Además de YAML y JSON, puedes usar SQLite como una base de datos ligera para proyectos más grandes que requieren consultas SQL:

---

```php
// Create an SQLite database | Crear una base de datos en SQLite
$db = DatabaseFactory::create('path/to/database.db', 'sqlite');
```

---

## 📌 **Use Cases** | **Casos de Uso**

- 💾 **Configuration Storage**: Save and load configurations in YAML or JSON efficiently.
- 🎮 **Scorekeeping Systems**: Use the database to store player statistics in games or applications.
- 🛠️ **Server Plugins**: Implement simple databases for plugins on platforms like PocketMine.

---

- 💾 **Almacenamiento de Configuraciones**: Guarda y carga configuraciones en formato YAML o JSON de forma eficiente.
- 🎮 **Sistemas de Puntuación**: Utiliza la base de datos para almacenar estadísticas de jugadores en juegos o aplicaciones.
- 🛠️ **Plugins de Servidores**: Implementa bases de datos simples para plugins en plataformas como PocketMine.

---

## 🤝 **Contributing** | **Contribuciones**

Contributions are welcome! If you want to improve this library, feel free to submit **pull requests** or open an **issue** in the repository.

---

¡Las contribuciones son bienvenidas! Si deseas mejorar esta librería, puedes enviar **pull requests** o abrir un **issue** en el repositorio.

---

### 📝 **Steps to Contribute** | **Pasos para Contribuir**

1. Clone the project:
   ```bash
   git clone https://github.com/no

zell/database-library.git
   ```
2. Create a new branch:
   ```bash
   git checkout -b feature/new-feature
   ```
3. Make your changes and submit a pull request.

---

1. Clona el proyecto:
   ```bash
   git clone https://github.com/nozell/database-library.git
   ```
2. Crea una nueva rama:
   ```bash
   git checkout -b feature/nueva-caracteristica
   ```
3. Haz tus cambios y envía tu pull request.

---

## 📜 **License** | **Licencia**

This project is licensed under the **Apache License 2.0**. You can view the full license in the [LICENSE](LICENSE) file.

---

Este proyecto está licenciado bajo la **Licencia Apache 2.0**. Puedes consultar el archivo [LICENSE](LICENSE) para más detalles.

---
