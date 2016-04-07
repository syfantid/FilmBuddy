package movies;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import database.*;

/**
 * Class to handle the MySQL Database functionality
 * Created by Sofia on 4/3/2016.
 */
public class MovieStorager {
    private static Connection conn;
    /**
     * Inserts a movie into the Database
     * @param m The movie to be inserted
     * @throws SQLException In case a connection is not open or there is a problem with the inserted values
     */
    public static void InsertMovietoDB(Movie m) throws SQLException {
        Database db = new Database();
        if (db.connect()) {
            conn = db.getConnection();

            // The mysql insert statement
            String query = " insert into all_movies (title, year, categories, synopsis, icon_url, cast, " +
                    "director, imdb_url, extended_plot)" + " values (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Create the mysql insert PreparedStatement
            PreparedStatement preparedStmt = conn.prepareStatement(query);
            preparedStmt.setString(1, m.getTitle());
            preparedStmt.setInt(2, m.getYear());
            preparedStmt.setString(3, m.getCategories());
            preparedStmt.setString(4, m.getSynopsis());
            preparedStmt.setString(5, m.getIconURL());
            preparedStmt.setString(6, m.getCast());
            preparedStmt.setString(7, m.getDirector());
            preparedStmt.setString(8, m.getImdbURL());
            preparedStmt.setString(9, m.getExtendedPlot());

            // Execute the PreparedStatement
            try {
                preparedStmt.execute();
            } catch (Exception e) {
                e.printStackTrace();
            }

            conn.close();
        }
    }
}
