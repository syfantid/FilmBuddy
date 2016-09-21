package movies_component;

import java.sql.SQLException;

/**
 * Class to centralize the collection of movie data
 * Created by Sofia on 4/10/2016.
 */
public class Collector {
    public static void main(String[] args) {
        // Collect all films from Wikipedia and insert them into a MySQL Database
        try {
            MovieCollectorSQL.main(null);
        } catch (Throwable throwable) {
            throwable.printStackTrace();
        }

        // Collect all films' metadata from OMDb and insert them into a MongoDB
        try {
            MovieStoragerMongo.main(args);
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
